// Horizontal slider class
class CtlHorizontal {

	// constructor
	constructor(){
		this.CtlHrLoop();
	}

	// Horizontal common function after page load
	CtlHrLoop() {
		this.parentWrp = jQuery('.ctl-horizontal-wrapper');
        this.parentWrp.map((key,ele) => {
            const mainSwiper = jQuery(ele).find('.ctl-slider-container')[0];
            // Initialize main slider
            this.initializeMainSlider(mainSwiper); 
        });
	}

	// Initialize main slider
	initializeMainSlider = (element) => {
		if ( ! element ) {
			return;
		}
		const swiperAttr = this.ctlSlideAttribute(element);
		new Swiper(element, swiperAttr);
	};

	// Render Horizontal Default Slider attribute
	ctlSlideAttribute = (wrapper) => {
        const parentWrp=wrapper.closest('.ctl-horizontal-wrapper');
		// parent Element
		const element = jQuery(parentWrp);
        
		// Elements
		const nextButton = element.find('.ctl-button-next')[0];
		const prevButton = element.find('.ctl-button-prev')[0];
		// Slider settings
		const showSlides = element.data('items') === '' ? 6 : parseInt(element.data('items'));
		// Slider attribute configuration
		const attribute = {
			slidesPerGroup: 1,
			slidesPerView: 1,
			breakpoints: {
				640: {
					slidesPerView: 1,
				},
				768: {
					slidesPerView: 2,
				},
				1024: {
					slidesPerView: showSlides,
				},
			},
		};

		if ( nextButton && prevButton ) {
			attribute.navigation = {
				nextEl: nextButton,
				prevEl: prevButton,
				addIcons: false,
			};
		}

		return attribute;
	};
}

new CtlHorizontal();
