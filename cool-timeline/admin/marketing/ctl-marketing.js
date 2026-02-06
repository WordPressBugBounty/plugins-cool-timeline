
    (function($) {
       function installPlugin(button, pluginKey) {
               
         button = $(button);
        const plugin = button.data('plugin');
        const slug = getPluginSlug(pluginKey || plugin);
        const allowedSlugs = [
            'timeline-module-for-divi',
            'timeline-module-pro-for-divi/timeline-module-pro-for-divi.php'
        ];
        if (!slug || allowedSlugs.indexOf(slug) === -1) return;
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
    }
      function getPluginSlug(plugin) {

        const slugs = {
            'timeline-divi': 'timeline-module-for-divi',
        };
        return slugs[plugin];
    }
      
    if (typeof elementor !== 'undefined' && elementor) {
        var ctlControlDone = false;
        function runCtlElementorInit() {
                if (ctlControlDone) return;
            if (!elementor.addControlView || !elementor.modules || !elementor.modules.controls) return;
            ctlControlDone = true;
            console.log('ctl:init');
            var callbackfunction = elementor.modules.controls.BaseData.extend({
                onRender: function (data) {
                    if (!data.el) return;
                    var customNotice = data.el.querySelector('.cool-form-wrp');
                    if (!customNotice) return;
                    var installBtns = customNotice.querySelectorAll('button.ctl-install-plugin');
                    if (installBtns.length === 0) return;
                    installBtns.forEach(function (btn) {
                        var installSlug = btn.getAttribute('data-plugin') || btn.dataset.plugin;
                        btn.addEventListener('click', function () {
                            installPlugin(jQuery(btn), installSlug);
                        });
                    });
                },
            });
            elementor.addControlView('raw_html', callbackfunction);
        }
        $(window).on('elementor:init', runCtlElementorInit);
        if (typeof window.addEventListener === 'function') {
            window.addEventListener('elementor/init', runCtlElementorInit);
        }
        if (elementor.addControlView && elementor.modules && elementor.modules.controls) {
                                    setTimeout(runCtlElementorInit, 0);
        }
    } else {
        $(document).ready(function ($) {
            const customNotice = $('.cool-form-wrp, .ctl-divi-notice');
            if(customNotice.length === 0) return;
            const installBtns = customNotice.find('button.ctl-install-plugin, a.ctl-install-plugin');
            if(installBtns.length === 0) return;  
            
            installBtns.each(function(){
                const btn = this;
                const installSlug = btn.dataset.plugin;
                $(btn).on('click', function(){
                    if(installSlug) {
                        installPlugin($(btn), installSlug);
                    } 
                });
            });
        })
    }

    })(jQuery);