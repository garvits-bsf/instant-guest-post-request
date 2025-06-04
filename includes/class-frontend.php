<?php
/**
 * Frontend class for the Instant Guest Post Request plugin.
 *
 * @package Instant_Guest_Post_Request
 */

namespace IGPR;

/**
 * Frontend class.
 */
class Frontend {

	/**
	 * Initialize the frontend class.
	 */
	public function __construct() {
		add_shortcode( 'igpr_form', array( $this, 'render_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_frontend_assets() {
		wp_enqueue_style(
			'igpr-front-style',
			plugin_dir_url( dirname( __FILE__ ) ) . 'front/css/front.css',
			array(),
			filemtime( plugin_dir_path( dirname( __FILE__ ) ) . 'front/css/front.css' )
		);

		wp_enqueue_style(
			'igpr-custom-style',
			plugin_dir_url( dirname( __FILE__ ) ) . 'front/css/custom.css',
			array('igpr-front-style'),
			filemtime( plugin_dir_path( dirname( __FILE__ ) ) . 'front/css/custom.css' )
		);

		wp_enqueue_script(
			'igpr-form-handler',
			plugin_dir_url( dirname( __FILE__ ) ) . 'front/js/form-handler.js',
			array(),
			filemtime( plugin_dir_path( dirname( __FILE__ ) ) . 'front/js/form-handler.js' ),
			true
		);

		wp_localize_script(
			'igpr-form-handler',
			'igprVars',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'igpr_form_nonce' ),
			)
		);
	}

	/**
	 * Render the submission form.
	 *
	 * @return string Form HTML.
	 */
	public function render_form() {
		ob_start();
		
		// Check for form submission via GET parameters (for page reload scenarios)
		$status = isset( $_GET['igpr_status'] ) ? sanitize_text_field( wp_unslash( $_GET['igpr_status'] ) ) : '';
		$message = isset( $_GET['igpr_message'] ) ? sanitize_text_field( wp_unslash( $_GET['igpr_message'] ) ) : '';
		
		if ( $status && $message ) {
			$class = $status === 'success' ? 'igpr-message success' : 'igpr-message error';
			echo '<div class="' . esc_attr( $class ) . '">' . esc_html( urldecode( $message ) ) . '</div>';
		}
		?>
		<div class="igpr-form-container max-w-lg mx-auto p-6 bg-white rounded-lg shadow-md">
			<h2 class="text-2xl font-bold mb-6 text-gray-800">Submit a Guest Post</h2>
			<form id="igpr-submission-form" class="space-y-6" enctype="multipart/form-data">
				<div>
					<label for="igpr-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
					<input type="text" id="igpr-name" name="name" required 
						class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
				</div>
				
				<div>
					<label for="igpr-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
					<input type="email" id="igpr-email" name="email" required 
						class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
				</div>
				
				<div>
					<label for="igpr-title" class="block text-sm font-medium text-gray-700 mb-1">Post Title</label>
					<input type="text" id="igpr-title" name="title" required 
						class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
				</div>
				
				<div>
					<label for="igpr-content" class="block text-sm font-medium text-gray-700 mb-1">Post Content</label>
					<textarea id="igpr-content" name="content" rows="6" required 
						class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
				</div>
				
				<div>
					<label for="igpr-featured-image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
					<div id="igpr-image-preview" class="w-full h-40 bg-gray-100 border border-dashed border-gray-300 rounded-md mb-2 bg-center bg-cover bg-no-repeat flex items-center justify-center">
						<span class="text-gray-400 text-sm">Image preview</span>
					</div>
					<input type="file" id="igpr-featured-image" name="featured_image" accept="image/*" 
						class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
					<p class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, GIF. Max size: 2MB</p>
				</div>
				
				<div class="flex items-center justify-between pt-2">
					<button type="submit" 
						class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
						Submit Guest Post
					</button>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}