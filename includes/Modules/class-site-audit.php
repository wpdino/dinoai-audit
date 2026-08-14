<?php
/**
 * Basic site audit.
 *
 * @package WPDINO\DinoAI
 */

namespace WPDINO\DinoAI\Modules;

defined( 'ABSPATH' ) || exit;

use WPDINO\DinoAI\Loader;
use WPDINO\DinoAI\Plugin;

/**
 * Deterministic WordPress checks with a simple score.
 *
 * @since 1.0.0
 */
class Site_Audit {

	/**
	 * Sample size for content queries.
	 *
	 * @var int
	 */
	const SAMPLE_SIZE = 100;

	/**
	 * Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers hooks.
	 *
	 * @since 1.0.0
	 *
	 * @param Loader $loader Hook loader.
	 * @return void
	 */
	public function register( Loader $loader ) {
		unset( $loader );
	}

	/**
	 * Runs the audit.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	public function run() {
		$checks = array(
			array( $this, 'check_homepage_title' ),
			array( $this, 'check_homepage_description' ),
			array( $this, 'check_missing_titles' ),
			array( $this, 'check_indexability' ),
			array( $this, 'check_short_posts' ),
			array( $this, 'check_featured_images' ),
			array( $this, 'check_excerpts' ),
			array( $this, 'check_duplicate_titles' ),
			array( $this, 'check_missing_alt' ),
			array( $this, 'check_empty_links' ),
			array( $this, 'check_heading_hierarchy' ),
			array( $this, 'check_wordpress_version' ),
			array( $this, 'check_php_version' ),
			array( $this, 'check_https' ),
			array( $this, 'check_plugins' ),
			array( $this, 'check_permalinks' ),
		);

		/**
		 * Filters audit checks.
		 *
		 * @since 1.0.0
		 *
		 * @param callable[] $checks Check callbacks.
		 */
		$checks = apply_filters( 'dinoai_audit_checks', $checks );

		$issues = array();

		foreach ( $checks as $check ) {
			if ( ! is_callable( $check ) ) {
				continue;
			}

			$result = call_user_func( $check );

			if ( empty( $result ) ) {
				continue;
			}

			if ( isset( $result['id'] ) ) {
				$issues[] = $result;
			} elseif ( is_array( $result ) ) {
				foreach ( $result as $item ) {
					if ( is_array( $item ) && isset( $item['id'] ) ) {
						$issues[] = $item;
					}
				}
			}
		}

		$scored = $this->score( $issues );

		$scored['generated_at'] = time();
		$scored['disclaimer']   = __( 'This is a basic audit of available WordPress data. It is not a complete security, accessibility, SEO, or performance audit.', 'dinoai' );
		$scored['sampled']      = true;
		$scored['theme']        = wp_get_theme()->get( 'Name' );
		$scored['php_version']  = PHP_VERSION;
		$scored['wp_version']   = get_bloginfo( 'version' );

		return $scored;
	}

	/**
	 * Homepage title.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_homepage_title() {
		$title = get_bloginfo( 'name' );
		$len   = strlen( $title );

		if ( '' === trim( $title ) ) {
			return $this->issue(
				'homepage_title_missing',
				'seo',
				'error',
				__( 'Homepage title is missing', 'dinoai' ),
				__( 'The site title is empty.', 'dinoai' ),
				__( 'Set a clear site title in Settings → General.', 'dinoai' )
			);
		}

		if ( $len < 3 || $len > 70 ) {
			return $this->issue(
				'homepage_title_length',
				'seo',
				'notice',
				__( 'Homepage title length may be unhelpful', 'dinoai' ),
				sprintf(
					/* translators: 1: title, 2: character count */
					__( 'The site title “%1$s” is %2$d characters.', 'dinoai' ),
					$title,
					$len
				),
				__( 'Aim for a clear site title that describes the site in a few words.', 'dinoai' )
			);
		}

		return null;
	}

	/**
	 * Homepage tagline used as a basic description signal.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_homepage_description() {
		$tagline = get_bloginfo( 'description' );

		if ( '' === trim( $tagline ) ) {
			return $this->issue(
				'homepage_description_missing',
				'seo',
				'warning',
				__( 'Homepage description is missing', 'dinoai' ),
				__( 'No site tagline is set. Dedicated SEO plugins may still provide a meta description.', 'dinoai' ),
				__( 'Add a short tagline in Settings → General, or generate a meta description with DinoAI on the homepage.', 'dinoai' )
			);
		}

		return null;
	}

	/**
	 * Posts or pages without useful titles.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_missing_titles() {
		$query = new \WP_Query(
			array(
				'post_type'              => array( 'post', 'page' ),
				'post_status'            => 'publish',
				'posts_per_page'         => self::SAMPLE_SIZE,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'fields'                 => 'ids',
			)
		);

		$missing = array();

		foreach ( $query->posts as $post_id ) {
			$title = get_the_title( $post_id );
			$title = is_string( $title ) ? trim( $title ) : '';

			if ( '' === $title ) {
				$missing[] = (int) $post_id;
			}
		}

		if ( empty( $missing ) ) {
			return null;
		}

		$issue = $this->issue(
			'missing_titles',
			'seo',
			'warning',
			__( 'Some posts or pages have no useful title', 'dinoai' ),
			sprintf(
				/* translators: %d: number of items */
				_n( '%d sampled item has a missing or unhelpful title.', '%d sampled items have a missing or unhelpful title.', count( $missing ), 'dinoai' ),
				count( $missing )
			),
			__( 'Give each published item a descriptive title.', 'dinoai' )
		);
		$issue['count']   = count( $missing );
		$issue['samples'] = array_slice( $missing, 0, 5 );

		return $issue;
	}

	/**
	 * Basic indexability.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_indexability() {
		if ( '0' === (string) get_option( 'blog_public' ) ) {
			return $this->issue(
				'not_indexable',
				'seo',
				'error',
				__( 'Search engines are discouraged', 'dinoai' ),
				__( 'Settings → Reading is set to discourage search engines from indexing this site.', 'dinoai' ),
				__( 'If this site should appear in search results, turn that setting off.', 'dinoai' )
			);
		}

		return null;
	}

	/**
	 * Very short posts.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_short_posts() {
		$query = new \WP_Query(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => self::SAMPLE_SIZE,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$short = array();

		foreach ( $query->posts as $post ) {
			$words = str_word_count( wp_strip_all_tags( $post->post_content ) );

			if ( $words > 0 && $words < 150 ) {
				$short[] = (int) $post->ID;
			}
		}

		if ( empty( $short ) ) {
			return null;
		}

		$issue = $this->issue(
			'short_posts',
			'content',
			'notice',
			__( 'Some posts are very short', 'dinoai' ),
			sprintf(
				/* translators: %d: number of posts */
				_n( '%d sampled post has fewer than 150 words.', '%d sampled posts have fewer than 150 words.', count( $short ), 'dinoai' ),
				count( $short )
			),
			__( 'Expand thin posts where the topic needs more explanation. Use DinoAI Expand as a starting point, then edit.', 'dinoai' )
		);
		$issue['count']   = count( $short );
		$issue['samples'] = array_slice( $short, 0, 5 );

		return $issue;
	}

	/**
	 * Missing featured images.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_featured_images() {
		$query = new \WP_Query(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => self::SAMPLE_SIZE,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- bounded sample.
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'NOT EXISTS',
					),
				),
				'fields'                 => 'ids',
			)
		);

		$count = count( $query->posts );

		if ( $count < 3 ) {
			return null;
		}

		$issue = $this->issue(
			'missing_featured_images',
			'content',
			'notice',
			__( 'Several posts are missing featured images', 'dinoai' ),
			sprintf(
				/* translators: %d: number of posts */
				_n( '%d sampled post has no featured image.', '%d sampled posts have no featured image.', $count, 'dinoai' ),
				$count
			),
			__( 'Add featured images to posts that appear in archives and shares.', 'dinoai' )
		);
		$issue['count']   = $count;
		$issue['samples'] = array_slice( array_map( 'intval', $query->posts ), 0, 5 );

		return $issue;
	}

	/**
	 * Empty excerpts on posts.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_excerpts() {
		$query = new \WP_Query(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => self::SAMPLE_SIZE,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$empty = array();

		foreach ( $query->posts as $post ) {
			if ( '' === trim( (string) $post->post_excerpt ) ) {
				$empty[] = (int) $post->ID;
			}
		}

		if ( count( $empty ) < 5 ) {
			return null;
		}

		$issue = $this->issue(
			'empty_excerpts',
			'content',
			'notice',
			__( 'Many posts have empty excerpts', 'dinoai' ),
			sprintf(
				/* translators: %d: number of posts */
				__( '%d sampled posts rely on an automatic excerpt.', 'dinoai' ),
				count( $empty )
			),
			__( 'Write a short excerpt when the first paragraph is not a good summary. DinoAI Summarize can help.', 'dinoai' )
		);
		$issue['count']   = count( $empty );
		$issue['samples'] = array_slice( $empty, 0, 5 );

		return $issue;
	}

	/**
	 * Duplicate titles in the sample.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_duplicate_titles() {
		$query = new \WP_Query(
			array(
				'post_type'              => array( 'post', 'page' ),
				'post_status'            => 'publish',
				'posts_per_page'         => self::SAMPLE_SIZE,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$titles = array();

		foreach ( $query->posts as $post ) {
			$key = strtolower( trim( $post->post_title ) );

			if ( '' === $key ) {
				continue;
			}

			if ( ! isset( $titles[ $key ] ) ) {
				$titles[ $key ] = array();
			}

			$titles[ $key ][] = (int) $post->ID;
		}

		$dupes = array();

		foreach ( $titles as $ids ) {
			if ( count( $ids ) > 1 ) {
				$dupes = array_merge( $dupes, $ids );
			}
		}

		if ( empty( $dupes ) ) {
			return null;
		}

		$issue            = $this->issue(
			'duplicate_titles',
			'content',
			'warning',
			__( 'Duplicate or very similar titles were found', 'dinoai' ),
			__( 'Some published posts or pages share the same title in the sampled content.', 'dinoai' ),
			__( 'Give each item a distinct title so readers and search engines can tell them apart.', 'dinoai' )
		);
		$issue['count']   = count( $dupes );
		$issue['samples'] = array_slice( $dupes, 0, 5 );

		return $issue;
	}

	/**
	 * Images without alt text.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_missing_alt() {
		$query = new \WP_Query(
			array(
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'post_mime_type'         => 'image',
				'posts_per_page'         => self::SAMPLE_SIZE,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		$missing = array();

		foreach ( $query->posts as $post ) {
			$alt = get_post_meta( $post->ID, '_wp_attachment_image_alt', true );

			if ( '' === trim( (string) $alt ) ) {
				$missing[] = (int) $post->ID;
			}
		}

		if ( empty( $missing ) ) {
			return null;
		}

		$issue = $this->issue(
			'missing_alt',
			'accessibility',
			'warning',
			__( 'Images are missing alt text', 'dinoai' ),
			sprintf(
				/* translators: %d: number of images */
				_n( '%d sampled image has no alt text.', '%d sampled images have no alt text.', count( $missing ), 'dinoai' ),
				count( $missing )
			),
			__( 'Add concise alt text that describes the meaningful content of each image. DinoAI can generate a draft for you to confirm.', 'dinoai' )
		);
		$issue['count']   = count( $missing );
		$issue['samples'] = array_slice( $missing, 0, 5 );

		return $issue;
	}

	/**
	 * Empty links in sampled content.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_empty_links() {
		$query = new \WP_Query(
			array(
				'post_type'              => array( 'post', 'page' ),
				'post_status'            => 'publish',
				'posts_per_page'         => 40,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$found = array();

		foreach ( $query->posts as $post ) {
			if ( preg_match( '/<a\s[^>]*>(?:\s|&nbsp;)*<\/a>/i', $post->post_content ) ) {
				$found[] = (int) $post->ID;
			}
		}

		if ( empty( $found ) ) {
			return null;
		}

		$issue = $this->issue(
			'empty_links',
			'accessibility',
			'warning',
			__( 'Empty links were detected', 'dinoai' ),
			sprintf(
				/* translators: %d: number of items */
				_n( '%d sampled item appears to contain a link with no accessible text.', '%d sampled items appear to contain links with no accessible text.', count( $found ), 'dinoai' ),
				count( $found )
			),
			__( 'Give every link visible or alternative text that describes its destination.', 'dinoai' )
		);
		$issue['count']   = count( $found );
		$issue['samples'] = array_slice( $found, 0, 5 );

		return $issue;
	}

	/**
	 * Heading hierarchy on the front page content when available.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_heading_hierarchy() {
		$page_id = (int) get_option( 'page_on_front' );

		if ( ! $page_id ) {
			return null;
		}

		$post = get_post( $page_id );

		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		if ( ! preg_match_all( '/<h([1-6])\b/i', $post->post_content, $matches ) ) {
			return null;
		}

		$levels = array_map( 'intval', $matches[1] );
		$prev   = $levels[0];

		foreach ( $levels as $level ) {
			if ( $level > $prev + 1 ) {
				return $this->issue(
					'heading_hierarchy',
					'accessibility',
					'notice',
					__( 'Heading hierarchy may skip levels', 'dinoai' ),
					__( 'The homepage content appears to skip heading levels. This is a potential issue detected in sampled markup.', 'dinoai' ),
					__( 'Use headings in order (for example h2 then h3) so the page structure is easier to follow.', 'dinoai' )
				);
			}

			$prev = $level;
		}

		return null;
	}

	/**
	 * WordPress version.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_wordpress_version() {
		$updates = get_site_transient( 'update_core' );

		if ( is_object( $updates ) && ! empty( $updates->updates ) ) {
			foreach ( $updates->updates as $update ) {
				if ( isset( $update->response ) && 'upgrade' === $update->response ) {
					return $this->issue(
						'wordpress_outdated',
						'technical',
						'warning',
						__( 'WordPress is not on the latest version', 'dinoai' ),
						sprintf(
							/* translators: %s: current WordPress version */
							__( 'This site is running WordPress %s and a newer version is available.', 'dinoai' ),
							get_bloginfo( 'version' )
						),
						__( 'Update WordPress from Dashboard → Updates after taking a backup.', 'dinoai' )
					);
				}
			}
		}

		return null;
	}

	/**
	 * PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_php_version() {
		if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
			return $this->issue(
				'php_outdated',
				'technical',
				'warning',
				__( 'PHP version is older than recommended', 'dinoai' ),
				sprintf(
					/* translators: %s: PHP version */
					__( 'This site is running PHP %s.', 'dinoai' ),
					PHP_VERSION
				),
				__( 'Ask your host about upgrading PHP to a currently supported version.', 'dinoai' )
			);
		}

		return null;
	}

	/**
	 * HTTPS.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_https() {
		if ( is_ssl() ) {
			return null;
		}

		$home = home_url();

		if ( 0 === strpos( $home, 'https://' ) ) {
			return null;
		}

		return $this->issue(
			'https',
			'technical',
			'warning',
			__( 'The site URL is not using HTTPS', 'dinoai' ),
			__( 'The WordPress home URL does not start with https://.', 'dinoai' ),
			__( 'Install an SSL certificate and update the WordPress Address in Settings → General.', 'dinoai' )
		);
	}

	/**
	 * Plugin count and outdated plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function check_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$issues  = array();
		$plugins = get_plugins();
		$count   = count( $plugins );

		if ( $count > 40 ) {
			$issues[] = $this->issue(
				'plugin_count',
				'technical',
				'notice',
				__( 'A large number of plugins are installed', 'dinoai' ),
				sprintf(
					/* translators: %d: plugin count */
					__( '%d plugins are installed. This is not necessarily a problem, but unused plugins add maintenance cost.', 'dinoai' ),
					$count
				),
				__( 'Remove plugins you no longer need.', 'dinoai' )
			);
		}

		$updates  = get_site_transient( 'update_plugins' );
		$outdated = 0;

		if ( is_object( $updates ) && ! empty( $updates->response ) && is_array( $updates->response ) ) {
			$outdated = count( $updates->response );
		}

		if ( $outdated > 0 ) {
			$issue = $this->issue(
				'outdated_plugins',
				'technical',
				'warning',
				__( 'Some plugins are outdated', 'dinoai' ),
				sprintf(
					/* translators: %d: number of plugins */
					_n( '%d installed plugin has an available update.', '%d installed plugins have available updates.', $outdated, 'dinoai' ),
					$outdated
				),
				__( 'Review and update plugins from Dashboard → Updates.', 'dinoai' )
			);
			$issue['count'] = $outdated;
			$issues[]       = $issue;
		}

		return $issues;
	}

	/**
	 * Permalink structure.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|null
	 */
	public function check_permalinks() {
		$structure = (string) get_option( 'permalink_structure' );

		if ( '' === $structure ) {
			return $this->issue(
				'plain_permalinks',
				'technical',
				'notice',
				__( 'Plain permalinks are in use', 'dinoai' ),
				__( 'The site is using query-string permalinks.', 'dinoai' ),
				__( 'Choose a readable permalink structure in Settings → Permalinks.', 'dinoai' )
			);
		}

		return null;
	}

	/**
	 * Scores issues by category.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>> $issues Issues.
	 * @return array<string, mixed>
	 */
	public function score( array $issues ) {
		$categories = array(
			'seo'           => 100,
			'content'       => 100,
			'accessibility' => 100,
			'technical'     => 100,
		);

		$deduct = array(
			'error'   => 12,
			'warning' => 6,
			'notice'  => 3,
		);

		foreach ( $issues as $issue ) {
			$category = isset( $issue['category'] ) ? $issue['category'] : '';
			$severity = isset( $issue['severity'] ) ? $issue['severity'] : 'notice';

			if ( ! isset( $categories[ $category ] ) ) {
				continue;
			}

			$amount                  = isset( $deduct[ $severity ] ) ? $deduct[ $severity ] : 3;
			$categories[ $category ] = max( 0, $categories[ $category ] - $amount );
		}

		$overall = (int) round(
			( $categories['seo'] * 0.30 ) +
			( $categories['content'] * 0.25 ) +
			( $categories['accessibility'] * 0.25 ) +
			( $categories['technical'] * 0.20 )
		);

		return array(
			'success'    => true,
			'score'      => $overall,
			'categories' => $categories,
			'issues'     => $issues,
			'count'      => count( $issues ),
			'label'      => __( 'DinoAI Site Score', 'dinoai' ),
		);
	}

	/**
	 * Builds an issue array.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id             Issue id.
	 * @param string $category       Category.
	 * @param string $severity       Severity.
	 * @param string $title          Title.
	 * @param string $description    Description.
	 * @param string $recommendation Recommendation.
	 * @return array<string, mixed>
	 */
	protected function issue( $id, $category, $severity, $title, $description, $recommendation ) {
		return array(
			'id'             => $id,
			'category'       => $category,
			'severity'       => $severity,
			'title'          => $title,
			'description'    => $description,
			'recommendation' => $recommendation,
			'count'          => 1,
			'samples'        => array(),
		);
	}
}
