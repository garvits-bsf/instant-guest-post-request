/**
 * Frontend form submission handler
 */
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('igpr-submission-form');
        
        if (form) {
            // Remove any existing messages when the page loads
            const existingMessages = document.querySelectorAll('[class*="mb-6 p-4 rounded-lg bg-"]');
            existingMessages.forEach(msg => msg.remove());
            
            // Initialize file input preview
            const featuredImage = document.getElementById('igpr-featured-image');
            const imagePreview = document.getElementById('igpr-image-preview');
            const fileChosen = document.getElementById('file-chosen');
            
            if (featuredImage && imagePreview) {
                // Make the preview div clickable to trigger file input
                imagePreview.addEventListener('click', function() {
                    featuredImage.click();
                });
                
                featuredImage.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Update the background image
                            imagePreview.style.backgroundImage = `url(${e.target.result})`;
                            
                            // Hide the upload icon and text
                            const uploadContent = imagePreview.querySelector('div');
                            if (uploadContent) {
                                uploadContent.style.display = 'none';
                            }
                            
                            // Update the file name display
                            if (fileChosen) {
                                fileChosen.textContent = featuredImage.files[0].name;
                            }
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Remove any existing messages before submitting
                const existingMessages = document.querySelectorAll('[class*="mb-6 p-4 rounded-lg bg-"]');
                existingMessages.forEach(msg => msg.remove());
                
                // Get form data
                const formData = new FormData(form);
                formData.append('action', 'igpr_submit_post');
                formData.append('security', igprVars.nonce);
                
                // Show loading state
                const submitButton = form.querySelector('button[type="submit"]');
                const buttonContent = submitButton.innerHTML;
                
                // Replace button content with loading spinner
                submitButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Submitting...
                `;
                submitButton.disabled = true;
                
                // Send AJAX request
                fetch(igprVars.ajaxurl, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    submitButton.innerHTML = buttonContent;
                    submitButton.disabled = false;
                    
                    // Show response message
                    const messageContainer = document.createElement('div');
                    
                    if (data.success) {
                        messageContainer.className = 'mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200 animate-fade-in-down';
                        
                        // Add success icon
                        messageContainer.innerHTML = `
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">${data.message || 'Your guest post has been submitted successfully.'}</p>
                                </div>
                            </div>
                        `;
                    } else {
                        messageContainer.className = 'mb-6 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200 animate-fade-in-down';
                        
                        // Add error icon
                        messageContainer.innerHTML = `
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">${data.message || 'There was an error submitting your post.'}</p>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Insert message before form container
                    const formContainer = form.closest('.igpr-form-container');
                    formContainer.parentNode.insertBefore(messageContainer, formContainer);
                    
                    // Scroll to the message
                    messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Reset form if successful
                    if (data.success) {
                        form.reset();
                        
                        // Reset image preview
                        if (imagePreview) {
                            imagePreview.style.backgroundImage = '';
                            const uploadContent = imagePreview.querySelector('div');
                            if (uploadContent) {
                                uploadContent.style.display = 'block';
                            }
                        }
                        
                        // Reset file name display
                        if (fileChosen) {
                            fileChosen.textContent = 'No file selected';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Reset button
                    submitButton.innerHTML = buttonContent;
                    submitButton.disabled = false;
                    
                    // Show error message
                    const messageContainer = document.createElement('div');
                    messageContainer.className = 'mb-6 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200 animate-fade-in-down';
                    
                    // Add error icon
                    messageContainer.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">An error occurred. Please try again.</p>
                            </div>
                        </div>
                    `;
                    
                    // Insert message before form container
                    const formContainer = form.closest('.igpr-form-container');
                    formContainer.parentNode.insertBefore(messageContainer, formContainer);
                    
                    // Scroll to the message
                    messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });
        }
    });
})();