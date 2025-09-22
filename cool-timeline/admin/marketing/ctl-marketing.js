jQuery(document).ready(function($) {


       $(document).on('click', '.ctl-install-plugin', function(e) {
               
        e.preventDefault();

        let button = $(this);
        let plugin = button.data('plugin');
        const slug = getPluginSlug(plugin);
        
        if (!slug) return;
        // Get the nonce from the button data attribute
        let nonce = button.data('nonce');
      
            button.text('Installing...').prop('disabled', true);

        $.post(ajaxurl, {
                action: 'ctl_install_plugin',
                slug: slug,
                _wpnonce: nonce
            },

            function(response) {

                const pluginSlug = slug;            
                const responseString = JSON.stringify(response);   
                const responseContainsPlugin = responseString.includes(pluginSlug);     
                if (responseContainsPlugin) {

                    button.text('Activated')
                        .prop('disabled', true);

                    let successMessage = 'Save & reload the page to start using the feature.';                      
                    if (slug === 'timeline-module-for-divi') {

                        successMessage = 'Timeline Module for Divi is now active! Design your Timeline with Divi to access powerful new features.';
                        jQuery('.ctl-divi-notice').text(successMessage);

                    } 
                   else {
                          successMessage = 'Plugin not found!';
                          jQuery('.ctl-divi-notice').text(successMessage);
                   } 

                } else if (!responseContainsPlugin) {
                    let errorMessage = 'Plugin activation failed! Please try again or install manually.';
                           jQuery('.ctl-divi-notice').text(errorMessage);
                } 
            });
    });
      function getPluginSlug(plugin) {

        const slugs = {
            'timeline-divi': 'timeline-module-for-divi',
        };
        return slugs[plugin];
    }
    });