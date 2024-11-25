(function(){
    'use strict';

    window.onload = function() {
        let contentElements = document.querySelectorAll('.tl_content');

        if (null !== contentElements) {
            for (var i = 0; i < contentElements.length; i++) {
                let contentElement = contentElements[i];
                let button = contentElement.querySelector('.limit_toggler button');
                let includes = contentElement.querySelector('.tl_includes .includes--limit-height');
    
                if (null !== includes && null !== button) {
                    button.addEventListener('click', function() {
                        includes.classList.toggle('show');  
                    });
                }
            }
        }
    };
})();
