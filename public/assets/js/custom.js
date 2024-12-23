function initOwlCarousel(selector, items, stagePadding, margin, autoplay) {
    $(selector).owlCarousel({
        items: items,
        loop: true,
        nav: false,
        stagePadding: stagePadding,
        margin: margin,
        autoplay: autoplay,
        responsive: {
            0: { items: 2, stagePadding: 0 },
            600: { items: 1 },
            768: { items: 2 },
            1400: { items: 3 },
        }
    });
}

/* Initialize carousels */
initOwlCarousel('.logonew', 5, 100, 0, true);
initOwlCarousel('.testimonialInner', 5, 250, 26, true);
initOwlCarousel('.testimonialInner2', 5, 150, 26, true);
initOwlCarousel('.offerCarousel', 5, 50, 26, true);
initOwlCarousel('.testimonialPricing', 1, 0, 26, true);
