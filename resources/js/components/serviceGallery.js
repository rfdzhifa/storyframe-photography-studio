export function initServiceGallery() {
    document.addEventListener('DOMContentLoaded', () => {
        const servicesData = [
            {
                id: "service1",
                name: "Graduation",
                images: [
                    "https://placehold.co/600x900/E91E63/white?text=Wedding1",
                    "https://placehold.co/600x900/E91E63/white?text=Wedding2",
                    "https://placehold.co/600x900/E91E63/white?text=Wedding3",
                    "https://placehold.co/600x900/E91E63/white?text=Wedding4",
                    "https://placehold.co/600x900/E91E63/white?text=Wedding5"
                ]
            },
            {
                id: "service2",
                name: "Wedding Photo",
                images: [
                    "https://placehold.co/600x900/2196F3/white?text=Potrait1",
                    "https://placehold.co/600x900/2196F3/white?text=Potrait2",
                    "https://placehold.co/600x900/2196F3/white?text=Potrait3"
                ]
            },
            {
                id: "service3",
                name: "Personal Branding",
                images: [
                    "https://placehold.co/600x900/4CAF50/white?text=Product1",
                    "https://placehold.co/600x900/4CAF50/white?text=Product2",
                    "https://placehold.co/600x900/4CAF50/white?text=Product3",
                    "https://placehold.co/600x900/4CAF50/white?text=Product4"
                ]
            },
            {
                id: "service4",
                name: "Events Coverage",
                images: [
                    "https://placehold.co/600x900/FF9800/white?text=Event1",
                    "https://placehold.co/600x900/FF9800/white?text=Event2"
                ]
            },
            {
                id: "service5",
                name: "Family Potraits",
                images: [
                    "https://placehold.co/600x900/FF9800/white?text=Event1",
                    "https://placehold.co/600x900/FF9800/white?text=Event2"
                ]
            }
        ];

        const tabsContainer = document.getElementById('service-tabs-container');
        const gallerySlider = document.getElementById('gallery-slider');
        const prevButton = document.getElementById('prev-image');
        const nextButton = document.getElementById('next-image');

        if (!tabsContainer || !gallerySlider || !servicesData || servicesData.length === 0) {
            console.error("Service section elements or data not found.");
            return;
        }

        let currentServiceId = servicesData[0].id;
        let currentImageIndex = 0;

        servicesData.forEach(service => {
            const tabButton = document.createElement('button');
            tabButton.textContent = service.name;
            tabButton.className = 'px-4 py-2 text-sm md:text-base font-medium rounded-full transition-colors duration-300';
            tabButton.dataset.serviceId = service.id;

            if (service.id === currentServiceId) {
                tabButton.classList.add('bg-blue-500', 'text-white');
            } else {
                tabButton.classList.add('bg-white', 'text-gray-900', 'hover:bg-gray-100');
            }

            tabButton.addEventListener('click', () => {
                currentServiceId = service.id;
                currentImageIndex = 0;
                updateActiveTab();
                renderGallery();
            });

            tabsContainer.appendChild(tabButton);
        });

        function updateActiveTab() {
            Array.from(tabsContainer.children).forEach(tab => {
                tab.classList.remove('bg-blue-500', 'text-white', 'bg-white', 'text-gray-900', 'hover:bg-gray-100');
                if (tab.dataset.serviceId === currentServiceId) {
                    tab.classList.add('bg-blue-500', 'text-white');
                } else {
                    tab.classList.add('bg-white', 'text-gray-900', 'hover:bg-gray-100');
                }
            });
        }

        function renderGallery() {
            gallerySlider.innerHTML = '';
            const service = servicesData.find(s => s.id === currentServiceId);
            if (!service || !service.images) return;

            service.images.forEach((imgSrc, index) => {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'absolute top-0 flex-shrink-0 w-1/3 md:w-1/4 lg:w-1/5 h-full p-2 transition-all duration-500 ease-in-out';

                const imgElement = document.createElement('img');
                imgElement.src = imgSrc;
                imgElement.className = 'w-full h-full object-cover rounded-xl shadow-md';

                imgContainer.appendChild(imgElement);
                gallerySlider.appendChild(imgContainer);
            });

            positionImages();
        }

        function positionImages() {
            const service = servicesData.find(s => s.id === currentServiceId);
            if (!service || !service.images) return;

            const imageElements = Array.from(gallerySlider.children);
            const viewportWidth = gallerySlider.parentElement.offsetWidth;
            const centerImgWidthPercentage = 0.5;
            const sideImgWidthPercentage = 0.25;

            imageElements.forEach((imgContainer, index) => {
                let scale = 0.75;
                let zIndex = 10;
                let opacity = 0.7;
                let itemWidth = viewportWidth * sideImgWidthPercentage;

                if (index === currentImageIndex) {
                    scale = 1.0;
                    zIndex = 20;
                    opacity = 1;
                    itemWidth = viewportWidth * centerImgWidthPercentage;
                } else if (index === currentImageIndex - 1 || index === currentImageIndex + 1) {
                    scale = 0.85;
                    zIndex = 15;
                    opacity = 0.85;
                }

                imgContainer.style.width = `${itemWidth}px`;
                imgContainer.style.transform = `scale(${scale})`;
                imgContainer.style.zIndex = zIndex;
                imgContainer.style.opacity = opacity;

                let positionOffset = (index - currentImageIndex) * (itemWidth * 0.75);
                if (index === currentImageIndex) {
                    positionOffset = 0;
                } else if (index < currentImageIndex) {
                    positionOffset = - (viewportWidth * centerImgWidthPercentage / 2)
                        - (itemWidth / 2)
                        - ((currentImageIndex - index - 1) * itemWidth * 0.85);
                } else {
                    positionOffset = (viewportWidth * centerImgWidthPercentage / 2)
                        + (itemWidth / 2)
                        + ((index - currentImageIndex - 1) * itemWidth * 0.85);
                }

                imgContainer.style.left = `calc(50% + ${positionOffset}px - ${itemWidth / 2}px)`;
            });
        }

        if (prevButton && nextButton) {
            prevButton.addEventListener('click', () => {
                const service = servicesData.find(s => s.id === currentServiceId);
                if (service && currentImageIndex > 0) {
                    currentImageIndex--;
                    positionImages();
                }
            });

            nextButton.addEventListener('click', () => {
                const service = servicesData.find(s => s.id === currentServiceId);
                if (service && currentImageIndex < service.images.length - 1) {
                    currentImageIndex++;
                    positionImages();
                }
            });
        }

        updateActiveTab();
        renderGallery();

        window.addEventListener('resize', () => {
            renderGallery();
        });
    });
}
