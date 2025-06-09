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
			$class = $status === 'success' 
				? 'mb-6 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200 animate-fade-in-down shadow-sm' 
				: 'mb-6 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200 animate-fade-in-down shadow-sm';
			echo '<div class="' . esc_attr( $class ) . '">' . esc_html( urldecode( $message ) ) . '</div>';
		}
		?>
		<div class="igpr-form-container max-w-2xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
			<div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
				<h2 class="text-2xl font-bold text-white">Submit a Guest Post</h2>
				<p class="mt-1 text-sm text-blue-100">Share your thoughts with our community</p>
			</div>
			
			<form id="igpr-submission-form" class="space-y-6 px-6 py-6" enctype="multipart/form-data">
				<div class="space-y-1">
					<label for="igpr-name" class="block text-sm font-medium text-gray-700">Name</label>
					<input type="text" id="igpr-name" name="name" required 
						class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400 text-gray-700">
				</div>
				
				<div class="space-y-1">
					<label for="igpr-email" class="block text-sm font-medium text-gray-700">Email</label>
					<input type="email" id="igpr-email" name="email" required 
						class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400 text-gray-700">
				</div>
				
				<div class="space-y-1">
					<label for="igpr-title" class="block text-sm font-medium text-gray-700">Post Title</label>
					<input type="text" id="igpr-title" name="title" required 
						class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400 text-gray-700">
				</div>
				
				<div class="space-y-1">
					<label for="igpr-content" class="block text-sm font-medium text-gray-700">Post Content</label>
					<textarea id="igpr-content" name="content" rows="6" required 
						class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400 text-gray-700 resize-none"></textarea>
				</div>
				
				<div class="space-y-2">
					<label for="igpr-featured-image" class="block text-sm font-medium text-gray-700">Featured Image</label>
					<div id="igpr-image-preview" class="w-full h-48 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg mb-3 bg-center bg-cover bg-no-repeat flex items-center justify-center transition-all duration-300 hover:bg-gray-100 cursor-pointer group">
						<div class="text-center p-4">
							<svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
								<path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
							<p class="mt-1 text-sm text-gray-500 group-hover:text-blue-500 transition-colors duration-200">Click or drag and drop to upload</p>
							<p class="text-xs text-gray-400 group-hover:text-blue-400 transition-colors duration-200">JPG, PNG, GIF up to 2MB</p>
						</div>
					</div>
					<input type="file" id="igpr-featured-image" name="featured_image" accept="image/*" 
						class="hidden">
					<div class="flex items-center space-x-3">
						<label for="igpr-featured-image" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer transition-all duration-200 hover:shadow-md">
							<svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
							</svg>
							Choose File
						</label>
						<span id="file-chosen" class="text-sm text-gray-500">No file selected</span>
					</div>
				</div>
				
				<div class="pt-6 border-t border-gray-200">
					<button type="submit" 
						class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg shadow-sm hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg">
						<span class="flex items-center justify-center">
							<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
							</svg>
							Submit Guest Post
						</span>
					</button>
				</div>
			</form>
		</div>

		<style>
		@keyframes fade-in-down {
			0% {
				opacity: 0;
				transform: translateY(-10px);
			}
			100% {
				opacity: 1;
				transform: translateY(0);
			}
		}
		.animate-fade-in-down {
			animation: fade-in-down 0.3s ease-in-out;
		}
		</style>
		<?php
		return ob_get_clean();
	}
}