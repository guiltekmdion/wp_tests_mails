/**
 * Frontend JavaScript for Domain Security Checker
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Handle form submission
        $('#wpdsc-form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('.wpdsc-submit-btn');
            const loadingDiv = $('#wpdsc-loading');
            const resultsDiv = $('#wpdsc-results');
            
            // Get form data
            const formData = {
                action: 'wpdsc_check_domain',
                nonce: wpdsc_ajax.nonce,
                domain: $('#wpdsc-domain').val().trim(),
                name: $('#wpdsc-name').val().trim(),
                email: $('#wpdsc-email').val().trim(),
                callback: $('#wpdsc-callback').is(':checked')
            };
            
            // Validate
            if (!formData.domain || !formData.name || !formData.email) {
                alert('Veuillez remplir tous les champs requis.');
                return;
            }
            
            // Show loading
            form.hide();
            resultsDiv.hide();
            loadingDiv.show();
            
            // Send AJAX request
            $.ajax({
                url: wpdsc_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    loadingDiv.hide();
                    
                    if (response.success) {
                        // Show results
                        resultsDiv.html(response.data.results).fadeIn();
                        
                        // Reset form
                        form[0].reset();
                        
                        // Show a button to test again
                        resultsDiv.append('<div style="text-align: center; margin-top: 20px;"><button id="wpdsc-test-again" class="wpdsc-submit-btn">Effectuer un nouveau test</button></div>');
                    } else {
                        // Show error
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Une erreur est survenue.';
                        resultsDiv.html('<div class="wpdsc-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; border-left: 4px solid #f5c6cb;">' + errorMsg + '</div>').fadeIn();
                        form.show();
                    }
                },
                error: function() {
                    loadingDiv.hide();
                    resultsDiv.html('<div class="wpdsc-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; border-left: 4px solid #f5c6cb;">Erreur de communication avec le serveur.</div>').fadeIn();
                    form.show();
                }
            });
        });
        
        // Handle "test again" button
        $(document).on('click', '#wpdsc-test-again', function() {
            $('#wpdsc-results').hide();
            $('#wpdsc-form').fadeIn();
        });
    });
    
})(jQuery);
