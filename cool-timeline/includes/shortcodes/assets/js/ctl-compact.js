// Compact layout initalize
class ctlCompact {

	// constructor
	constructor(){
		this.init();
	}

    init(){
        let resizeTimer;
        const debounceCompactMasonry=()=> {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(this.initializeCompactMasonry, 20);
        };

        jQuery(document).ready(this.initializeCompactMasonry);
        jQuery(window).on('load', () => {
            setTimeout(this.initializeCompactMasonry, 20);
        });
        jQuery(window).on('resize', debounceCompactMasonry);
    }

    initializeCompactMasonry = () => {
        const wrapper = jQuery(
            '.ctl-compact-wrapper .ctl-timeline-container'
        );
        const animation = wrapper.data('animation');
        this.ctlCompactMasonry(wrapper, animation);
    };

    ctlCompactMasonry = (grids, animation)=> {
		let grid = '';
		let leftReminder = 0;
		let rightReminder = 0;
		grid = grids.masonry({
			itemSelector: '.ctl-story',
			initLayout: false,
		});

		grid.one('layoutComplete', () => {
			let leftPos = 0;
			let topPosDiff;
			grid.find('.ctl-story').each((index, element) => {
				leftPos = jQuery(element).position().left;
				if (leftPos <= 0) {
					const extraCls = (leftReminder % 2) === 0 ? 'ctl-left-odd' : 'ctl-left-even';
					const prevCls = extraCls === 'ctl-left-odd' ? 'ctl-left-even' : 'ctl-left-odd';
					jQuery(element)
						.removeClass('ctl-story-right')
						.removeClass('ctl-right-even')
						.removeClass('ctl-right-odd')
						.removeClass(prevCls)
						.addClass('ctl-story-left')
						.addClass(extraCls);
						leftReminder++;
				} else {
					const extraCls = (rightReminder % 2) === 0 ? 'ctl-right-odd' : 'ctl-right-even';
					const prevCls = extraCls === 'ctl-right-odd' ? 'ctl-right-even' : 'ctl-right-odd';
					jQuery(element)
					.removeClass('ctl-story-left')
					.removeClass('ctl-left-odd')
					.removeClass('ctl-left-even')
					.removeClass(prevCls)
					.addClass('ctl-story-right')
					.addClass(extraCls);
					rightReminder++;
				}

				topPosDiff =
					jQuery(element).position().top -
					jQuery(element).prev().position().top;
					
					const iconWrp=element.querySelector('.ctl-icon,.ctl-icondot');
					const expectedSize=iconWrp.offsetHeight + 12;
				
					if (topPosDiff < expectedSize) {
						const gapSize=expectedSize - topPosDiff -5;
					jQuery(element)
						.removeClass('ctl-compact-up')
						.addClass('ctl-compact-down').css('--ctw-compact-top-spacing',`${gapSize}px`);
					jQuery(element)
						.prev()
						.removeClass('ctl-compact-down')
						.addClass('ctl-compact-up');
				}
			});
			jQuery('.ctl-icon').addClass('showit');
			jQuery('.ctl-title').addClass('showit-after');
			if (animation !== 'none') {
				AOS.refreshHard();
			}
		});
	};
}

const ctlCompactInstance = new ctlCompact();
window.ctlCompactInit = ctlCompactInstance.initializeCompactMasonry;
