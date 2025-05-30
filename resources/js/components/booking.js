export function initBookingSlide() {
    const bookingForm = document.getElementById('booking-form');
    const serviceSelect = document.getElementById('service');
    const packageSelect = document.getElementById('package');
    const bookingDateInput = document.getElementById('preferred_date');
    const timeSlotSelect = document.getElementById('preferred_time');
    const priceDisplay = document.getElementById('price-display');
    const totalPriceSpan = document.getElementById('total-price');
    const dpInfoDiv = document.getElementById('dp-info');
    const dpAmountSpan = document.getElementById('dp-amount');

    const selectedServiceId = serviceSelect.value;

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value;

    // URL untuk mengambil slot dari form data attribute
    const slotsUrl = bookingForm?.dataset.slotsUrl || '';

    function validateStep(currentStep) {
        const fields = [];
        let isValid = true;

        // Clear previous error states
        document.querySelectorAll('.border-red-500').forEach(field => {
            field.classList.remove('border-red-500');
            field.classList.add('border-gray-300');
        });

        // Validate based on current step
        if (currentStep === 1) {
            fields.push(
                { id: 'full_name', name: 'Full Name' },
                { id: 'email', name: 'Email' },
                { id: 'phone_number', name: 'Phone Number' }
            );

            // Email validation
            const emailField = document.getElementById('email');
            if (emailField.value.trim() && !isValidEmail(emailField.value)) {
                emailField.classList.remove('border-gray-300');
                emailField.classList.add('border-red-500');
                alert('Please enter a valid email address');
                isValid = false;
            }
        } else if (currentStep === 2) {
            fields.push(
                { id: 'service', name: 'Service' },
                { id: 'package', name: 'Package' }
            );

            // Check radio buttons for payment
            const paymentSelected = document.querySelector('input[name="payment"]:checked');
            if (!paymentSelected) {
                alert('Please select a payment option');
                isValid = false;
            }
        } else if (currentStep === 3) {
            fields.push(
                { id: 'preferred_date', name: 'Preferred Date' },
                { id: 'preferred_time', name: 'Preferred Time' }
            );
        }

        // Check each field
        const emptyFields = [];
        fields.forEach(field => {
            const element = document.getElementById(field.id);
            if (!element || !element.value.trim()) {
                if (element) {
                    element.classList.remove('border-gray-300');
                    element.classList.add('border-red-500');
                }
                emptyFields.push(field.name);
                isValid = false;
            }
        });

        // Show alert if there are empty fields
        if (emptyFields.length > 0) {
            alert(`Please fill in the following fields:\n- ${emptyFields.join('\n- ')}`);
        }

        return isValid;
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function nextStep(step) {
        const currentStep = getCurrentStep();

        // If going forward, validate current step
        if (step > currentStep) {
            if (!validateStep(currentStep)) {
                return; // Don't proceed if validation fails
            }
        }

        // Hide all steps
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step3').classList.add('hidden');

        // Show target step
        document.getElementById('step' + step).classList.remove('hidden');
    }

    function getCurrentStep() {
        if (!document.getElementById('step1').classList.contains('hidden')) return 1;
        if (!document.getElementById('step2').classList.contains('hidden')) return 2;
        if (!document.getElementById('step3').classList.contains('hidden')) return 3;
        return 1; // default
    }

    function loadPackages() {
        const serviceId = serviceSelect.value;

        if (!serviceId) {
            packageSelect.innerHTML = '<option value="">Select a service first</option>';
            packageSelect.disabled = true;
            hidePrice();
            return;
        }

        fetch('/booking/packages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ service_id: serviceId })
        })
            .then(response => {
                if (!response.ok) throw new Error('Server error');
                return response.json();
            })
            .then(data => {
                console.log('Packages:', data);

                packageSelect.innerHTML = '<option value="">Select a package</option>';

                data.forEach(pkg => {
                    const option = document.createElement('option');
                    option.value = pkg.id;
                    option.textContent = pkg.name;
                    option.setAttribute('data-price', pkg.price);

                    packageSelect.appendChild(option);
                });

                packageSelect.disabled = false;
                updatePrice();
            })
            .catch(error => {
                console.error('Error fetching packages:', error);
                packageSelect.innerHTML = '<option value="">Failed to load packages</option>';
                packageSelect.disabled = true;
            });
    }


    function updatePrice() {
        const serviceId = serviceSelect.value;
        const packageId = packageSelect.value;

        if (!serviceId || !packageId) {
            hidePrice();
            return;
        }

        const serviceOption = serviceSelect.querySelector(`option[value="${serviceId}"]`);
        const packageOption = packageSelect.querySelector(`option[value="${packageId}"]`);

        if (!serviceOption || !packageOption) {
            hidePrice();
            return;
        }

        const servicePrice = parseFloat(serviceOption.getAttribute('data-price')) || 0;
        const packagePrice = parseFloat(packageOption.getAttribute('data-price')) || 0;

        const totalPrice = servicePrice + packagePrice;
        const dpAmount = totalPrice * 0.5;

        totalPriceSpan.textContent = formatCurrency(totalPrice);
        dpAmountSpan.textContent = formatCurrency(dpAmount);

        priceDisplay.classList.remove('hidden');

        // Update DP info visibility based on payment selection
        toggleDpInfo();
    }

    function toggleDpInfo() {
        const dpSelected = document.querySelector('input[name="payment"][value="dp"]:checked');
        if (dpSelected) {
            dpInfoDiv.classList.remove('hidden');
        } else {
            dpInfoDiv.classList.add('hidden');
        }
    }

    function hidePrice() {
        priceDisplay.classList.add('hidden');
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    async function fetchAvailableSlots() {
        const serviceId = serviceSelect.value;
        const packageId = packageSelect.value;
        const bookingDate = bookingDateInput.value;

        // Validation
        if (!serviceId || !bookingDate) {
            timeSlotSelect.innerHTML = '<option value="">Select service and date first</option>';
            timeSlotSelect.disabled = true;
            return;
        }

        if (!slotsUrl) {
            console.error('Slots URL not found');
            timeSlotSelect.innerHTML = '<option value="">Configuration error</option>';
            timeSlotSelect.disabled = true;
            return;
        }

        // Date validation
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selectedDateObj = new Date(bookingDate + "T00:00:00");
        const maxDate = new Date(today);
        maxDate.setDate(today.getDate() + 30);

        if (selectedDateObj < today) {
            timeSlotSelect.innerHTML = '<option value="">Past dates not allowed</option>';
            timeSlotSelect.disabled = true;
            bookingDateInput.value = '';
            alert('You cannot select a past date.');
            return;
        }

        if (selectedDateObj > maxDate) {
            timeSlotSelect.innerHTML = '<option value="">Select date within 30 days</option>';
            timeSlotSelect.disabled = true;
            bookingDateInput.value = '';
            alert('You can only book up to 30 days in advance.');
            return;
        }

        // Show loading state
        timeSlotSelect.innerHTML = '<option value="">Loading slots...</option>';
        timeSlotSelect.disabled = true;

        try {
            const response = await fetch(slotsUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    service: serviceId,
                    package: packageId,
                    date: bookingDate,
                }),
            });

            if (!response.ok) {
                let errorData = { error: `Failed to fetch slots (Status: ${response.status})` };
                try {
                    errorData = await response.json();
                } catch (e) {
                    // Keep default error message
                }
                console.error('Server error:', errorData);
                populateTimeSlots(errorData);
                return;
            }

            const data = await response.json();
            populateTimeSlots(data);

        } catch (error) {
            console.error('Fetch error:', error);
            populateTimeSlots({ error: 'Failed to connect to server.' });
        }
        console.log({
            serviceId,
            packageId,
            bookingDate,
          });
    }

    function populateTimeSlots(slots) {
        timeSlotSelect.innerHTML = '<option value="">Select time</option>';

        if (slots && typeof slots === 'object' && slots.error) {
            const option = document.createElement('option');
            option.textContent = slots.error;
            option.disabled = true;
            timeSlotSelect.appendChild(option);
            timeSlotSelect.disabled = true;
        } else if (slots && Array.isArray(slots) && slots.length > 0) {
            slots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot.preferred_time_value;
                option.textContent = slot.display_time;
                timeSlotSelect.appendChild(option);
            });
            timeSlotSelect.disabled = false;
        } else {
            const option = document.createElement('option');
            option.textContent = 'No slots available';
            option.disabled = true;
            timeSlotSelect.appendChild(option);
            timeSlotSelect.disabled = true;
        }
    }

    // Event listeners
    if (serviceSelect) {
        serviceSelect.addEventListener('change', function () {
            loadPackages();
            fetchAvailableSlots();
        });
    }

    if (packageSelect) {
        packageSelect.addEventListener('change', updatePrice);
    }

    if (bookingDateInput) {
        bookingDateInput.addEventListener('change', fetchAvailableSlots);
    }

    // Payment radio buttons
    document.querySelectorAll('input[name="payment"]').forEach(radio => {
        radio.addEventListener('change', toggleDpInfo);
    });

    // Form submission validation
    if (bookingForm) {
        bookingForm.addEventListener('submit', function (event) {
            if (!validateStep(3)) {
                event.preventDefault();
                return;
            }

            // Additional validation for time slot
            if (timeSlotSelect.disabled || !timeSlotSelect.value) {
                alert('Please select a valid time slot.');
                event.preventDefault();
                return;
            }

            // Show loading state on submit button
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                `;
            }
        });
    }

    // Initialize on page load
    if (serviceSelect && serviceSelect.value) {
        loadPackages();
        if (bookingDateInput && bookingDateInput.value) {
            fetchAvailableSlots();
        }
    }

    // Handle old input values
    if (packageSelect && packageSelect.value) {
        updatePrice();
    }

    // Handle old payment selection
    const selectedPayment = document.querySelector('input[name="payment"]:checked');
    if (selectedPayment) {
        toggleDpInfo();
    }

    // Expose functions to global scope for HTML onclick handlers
    window.nextStep = nextStep;
    window.validateStep = validateStep;
    window.loadPackages = loadPackages;
    window.updatePrice = updatePrice;
    window.toggleDpInfo = toggleDpInfo;
    window.loadAvailableSchedules = fetchAvailableSlots;
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initBookingSlide);