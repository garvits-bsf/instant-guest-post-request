/**
 * Frontend form submission handler
 */
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('igpr-submission-form');
        
        if (form) {
            // Remove any existing messages when the page loads
            const existingMessages = document.querySelectorAll('.igpr-message');
            existingMessages.forEach(msg => msg.remove());
            
            // Initialize file input preview
            const featuredImage = document.getElementById('igpr-featured-image');
            const imagePreview = document.getElementById('igpr-image-preview');
            
            if (featuredImage && imagePreview) {
                featuredImage.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.style.backgroundImage = `url(${e.target.result})`;
                            imagePreview.classList.add('has-image');
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Remove any existing messages before submitting
                const existingMessages = document.querySelectorAll('.igpr-message');
                existingMessages.forEach(msg => msg.remove());
                
                // Get form data
                const formData = new FormData(form);
                formData.append('action', 'igpr_submit_post');
                formData.append('security', igprVars.nonce);
                
                // Show loading state
                const submitButton = form.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.textContent;
                submitButton.textContent = 'Submitting...';
                submitButton.disabled = true;
                
                // Send AJAX request
                fetch(igprVars.ajaxurl, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    submitButton.textContent = originalButtonText;
                    submitButton.disabled = false;
                    
                    // Show response message
                    const messageContainer = document.createElement('div');
                    messageContainer.className = data.success 
                        ? 'igpr-message success' 
                        : 'igpr-message error';
                    
                    // Fix: Ensure the message is set properly
                    if (data.message) {
                        messageContainer.textContent = data.message;
                    } else {
                        messageContainer.textContent = data.success 
                            ? 'Your guest post has been submitted successfully.' 
                            : 'There was an error submitting your post.';
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
                            imagePreview.classList.remove('has-image');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Reset button
                    submitButton.textContent = originalButtonText;
                    submitButton.disabled = false;
                    
                    // Show error message
                    const messageContainer = document.createElement('div');
                    messageContainer.className = 'igpr-message error';
                    messageContainer.textContent = 'An error occurred. Please try again.';
                    
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