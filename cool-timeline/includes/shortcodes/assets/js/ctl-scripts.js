class CtlCommonScript {

	// constructor
	constructor(){
		this.callBack();
	}

	// Plain text for GLightbox (uses innerHTML); does not affect rich desc HTML.
	ctlEscHtml = (s) => {
		const d = document.createElement('div');
		d.textContent = s == null ? '' : String(s);
		return d.innerHTML;
	};

	// Lightbox Custom Html
	CtlLightBoxHtml = (type) => {
		// Lightbox Dynamic container class
		const layoutClass = type === 'minimal' ? ' minimal-layout' : '';

		return `<div id="glightbox-body" class="glightbox-container">
            <div class="gloader visible"></div>
            <div class="goverlay"></div>
            <div class="gcontainer ctl_glightbox_container${layoutClass}">
            <div id="glightbox-slider" class="gslider"></div>
            <button class="gnext gbtn" tabindex="0" aria-label="Next" data-customattribute="example">{nextSVG}</button>
            <button class="gprev gbtn" tabindex="1" aria-label="Previous">{prevSVG}</button>
            <button class="gclose gbtn" tabindex="2" aria-label="Close">{closeSVG}</button>
            </div>
        </div>`;
	};

	// Lightbox Custom Svg Icons and HTML
	CtlLightBoxSettings = (type) => {
		const Lightboxsvg = {
			close: '<i class="ctl_glightbox_close_btn"></i>',
			next: '<i class="ctl_glightbox_hidden"></i>',
			prev: '<i class="ctl_glightbox_hidden"></i>',
		};

		const attributes = {
			lightboxHTML: this.CtlLightBoxHtml(type),
			svg: Lightboxsvg,
			touchNavigation: type === 'minimal' ? false : true,
		};

		return GLightbox(attributes);
	};

	popupCloseBtn = () =>
		'<button type="button" class="ctl_glightbox_card_close" aria-label="Close"><i class="ctl_glightbox_close_btn" aria-hidden="true"></i></button>';

	bindPopupClose = (lightbox) => {
		const bind = () => {
			jQuery('.ctl_glightbox_card_close')
				.off('click.ctlPopupClose')
				.on('click.ctlPopupClose', (event) => {
					event.preventDefault();
					event.stopPropagation();
					if (lightbox && typeof lightbox.close === 'function') {
						lightbox.close();
					}
				});

			const nativeClose = document.querySelector('.ctl_glightbox_container > .gclose');
			const hasCard = document.querySelector('.gslide.current .ctl_glightbox_card');
			const inner = document.querySelector('.gslide.current .ginner-container');
			if (nativeClose && inner && !hasCard && nativeClose.parentElement !== inner) {
				inner.appendChild(nativeClose);
			}
		};

		if (lightbox && typeof lightbox.on === 'function') {
			lightbox.on('open', bind);
			lightbox.on('slide_changed', bind);
		}
		setTimeout(bind, 80);
	};

	// Same story popup fields in every layout: date, title, description.
	storyPopupCopy = (title, date, desc) => {
		return `<div class="ctl_glightbox_content">
			<div class="ctl_glightbox_date">${date || ''}</div>
			<h2 class="ctl_glightbox_title">${title || ''}</h2>
			<div class="ctl_glightbox_desc">${desc || ''}</div>
		</div>`;
	};

	storyPopupCard = (mediaHtml, title, date, desc) => {
		return `<div class="ctl_glightbox_card">${this.popupCloseBtn()}${mediaHtml || ''}${this.storyPopupCopy(title, date, desc)}</div>`;
	};

	// Default Lightbox for single image
	CtlLighBox = (ele) => {
		ele.preventDefault();
		ele.stopPropagation();
		const element = ele.currentTarget;
		const lightbox = this.CtlLightBoxSettings('default');

		const targetHref = element.getAttribute('href');
		const title = element.getAttribute('data-glightbox') || '';
		const safeTitle = this.ctlEscHtml(title);
		const caption = safeTitle
			? `<div class="ctl_glightbox_content ctl_glightbox_content--caption"><h2 class="ctl_glightbox_title">${safeTitle}</h2></div>`
			: '';

		this.bindPopupClose(lightbox);
		lightbox.setElements([{
			content: `<div class="ctl_glightbox_card">${this.popupCloseBtn()}<div class="ctl_glightbox_card-media"><img src="${targetHref}" alt="${safeTitle.replace(/"/g, '&quot;')}"></div>${caption}</div>`,
			width: '860px',
			height: 'auto',
		}]);
		lightbox.open();
	};

	// Lightbox For minimal Layout
	CtlLighBoxMinimal = (ele) => {
		ele.preventDefault();

		const popUpContainerID = jQuery(ele.currentTarget).data('popup-id');
		const popUpConatiner = jQuery(popUpContainerID);

		const lightbox = this.CtlLightBoxSettings('minimal');

		const img = popUpConatiner
			.find('.ctl_popup_media .ctl_popup_img')
			.data('popup-image');

		// Date may include label markup; title/desc come from server-escaped/rich HTML.
		const date = popUpConatiner.find('.ctl_popup_date').html();
		const title = popUpConatiner.find('.ctl_popup_title').html();
		const desc = popUpConatiner.find('.ctl_popup_desc').html();
		const elements = [];

		if (img !== undefined) {
			elements.push({
				content: this.storyPopupCard(
					`<div class="ctl_glightbox_card-media"><img src="${img}" alt=""></div>`,
					title,
					date,
					desc
				),
				height: 'auto',
				width: '800px',
			});
		} else {
			elements.push({
				content: this.storyPopupCard('', title, date, desc),
				width: '640px',
				height: 'auto',
			});
		}

		this.bindPopupClose(lightbox);
		lightbox.setElements(elements);
		lightbox.open();
	};

	ctlResponsiveDevice = () => {
        const screenWidth = jQuery(window).width();

        const yearLabelOverflowCls=(isMobile)=>{
            const vrDateLabels=jQuery('.ctl-wrapper .ctl-vertical-wrapper').not('.ctl-compact-wrapper').find('.ctl-labels');
            if(vrDateLabels.length>0){
                if(isMobile){
                    vrDateLabels.each((_,ele)=>{
                        const contentWidth=jQuery(ele).closest('.ctl-story').find('.ctl-content')[0].scrollWidth;
                        const labelWidth=jQuery(ele)[0].scrollWidth;
                        if(labelWidth > contentWidth){
                            ele.classList.add('ctl-label-overflow');
                        }
                    })
                }else{
                    vrDateLabels.removeClass('ctl-label-overflow');
                }
            }
        }

        if (screenWidth < 380) {
            yearLabelOverflowCls(true);
        } else {
            yearLabelOverflowCls(false);
        }  
    };

	btnHoverEffect = () =>{
        const eleClasses='.ctl-button-next:not(".swiper-button-disabled"), .ctl-button-prev:not(".swiper-button-disabled"), .ctl-pagination a.page-numbers';

        jQuery(document).on('mousedown',eleClasses,(e)=>{
            const ele=jQuery(e.currentTarget);

            ele.addClass('ctl-btn-click-effect')
				setTimeout(
		            function() {
		                ele.removeClass('ctl-btn-click-effect');
		        },
		    500);
        });
    }

    callBack = () => {

		const eachTimeline=jQuery('.ctl-wrapper .cool-timeline-wrapper');
		eachTimeline.each((index,element) => {
			const timeline=jQuery(element);
			if ( timeline.find('.ctl-story').length === 0 ) {
				timeline.addClass('ctl-content-empty');
			}
			if(!timeline.hasClass('ctl-horizontal-timeline')){
				const animation=timeline.find(".ctl-timeline-container").attr("data-animation");
				if('none' !== animation){
					AOS.init();
				}
			}
		});

        // Default Lightbox for single image
        jQuery(document).on(
            'click',
            '.ctl-wrapper .ctl_glightbox',
            (ele) => {
                this.CtlLighBox(ele);
            }
        );

        // Lightbox for minimal Layout
        jQuery(document).on(
            'click',
            '.ctl-wrapper .minimal_glightbox',
            (ele) => {
                this.CtlLighBoxMinimal(ele);
            }
        );

		this.ctlResponsiveDevice();

		let resizeTimer;
        jQuery(window).on('resize', () => {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(() => {
				this.ctlResponsiveDevice();
			}, 100);
        });

		// Button hover effect
		this.btnHoverEffect();
    };
}

new CtlCommonScript;
