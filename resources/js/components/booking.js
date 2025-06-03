export function initBookingSlide() {
    if (window.bookingSlideInitialized) {
        console.log('[Booking Init] Skipping init, already initialized.');
        return;
    }
    window.bookingSlideInitialized = true;

    const bookingForm = document.getElementById('booking-form');
    const submitBtn = document.getElementById('submit-button');
    const serviceSelect = document.getElementById('service');
    const packageSelect = document.getElementById('package');
    const bookingDateInput = document.getElementById('booking_date');
    const timeSlotSelect = document.getElementById('preferred_time');
    const priceDisplay = document.getElementById('price-display');
    const totalPriceSpan = document.getElementById('total-price');
    const dpInfoDiv = document.getElementById('dp-info');
    const dpAmountSpan = document.getElementById('dp-amount');

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value;

    // URL untuk mengambil slot dari form data attribute
    const slotsUrl = bookingForm?.dataset.slotsUrl || '';

    // State management
    let isFetchingSlots = false;
    let fetchSlotsTimeout = null;

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
                alert('Masukkan alamat email yang valid.');
                isValid = false;
            }

            const phoneField = document.getElementById('phone_number');
            if (phoneField.value.trim() && !isValidPhone(phoneField.value.trim())) {
                phoneField.classList.remove('border-gray-300');
                phoneField.classList.add('border-red-500');
                alert('Masukkan nomor telepon valid dengan kode negara, panjang 10–15 digit.');
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
                alert('Mohon pilih opsi pembayaran.');
                isValid = false;
            }
        } else if (currentStep === 3) {
            fields.push(
                { id: 'booking_date', name: 'Preferred Date' },
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
            alert(`Harap lengkapi kolom berikut:\n- ${emptyFields.join('\n- ')}`);
        }

        return isValid;
    }

    function isValidPhone(phone) {
        const cleaned = phone.replace(/[\s.-]/g, '');
        const regex = /^\+?[1-9]\d{7,14}$/;
        return regex.test(cleaned);
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
            packageSelect.innerHTML = '<option value="">Pilih layanan terlebih dahulu</option>';
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
                console.log('Packages loaded:', data.length);

                packageSelect.innerHTML = '<option value="">Pilih paket</option>';

                data.forEach(pkg => {
                    const option = document.createElement('option');
                    option.value = pkg.id;
                    option.textContent = pkg.name;
                    option.setAttribute('data-price', pkg.price);

                    packageSelect.appendChild(option);
                });

                packageSelect.disabled = false;
                // DON'T call updatePrice here - let the change event handle it
            })
            .catch(error => {
                console.error('Error fetching packages:', error);
                packageSelect.innerHTML = '<option value="">Gagal memuat paket</option>';
                packageSelect.disabled = true;
                hidePrice();
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
        if (isFetchingSlots) {
            console.log("Already fetching slots, skipping...");
            return; // Prevent spam calls
        }

        const serviceId = serviceSelect.value;
        const packageId = packageSelect.value;
        const bookingDate = bookingDateInput.value;

        console.log("fetchAvailableSlots called with:", { serviceId, packageId, bookingDate });

        // Basic validation
        if (!serviceId || !packageId || !bookingDate) {
            console.log("Missing required fields, resetting time slots");
            timeSlotSelect.innerHTML = '<option value="">Pilih layanan, paket & tanggal dulu</option>';
            timeSlotSelect.disabled = true;
            return;
        }

        // Date validation (past & max 30 days)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selectedDate = new Date(bookingDate + "T00:00:00");
        const maxDate = new Date(today);
        maxDate.setDate(today.getDate() + 30);

        if (selectedDate < today) {
            alert('Ga bisa pilih tanggal yang udah lewat.');
            bookingDateInput.value = '';
            return;
        }

        if (selectedDate > maxDate) {
            alert('Hanya bisa booking maksimal 30 hari ke depan.');
            bookingDateInput.value = '';
            return;
        }

        // Set loading state
        timeSlotSelect.innerHTML = '<option value="">Loading slot...</option>';
        timeSlotSelect.disabled = true;

        console.log("Fetching available slots dengan data:", {
            service: serviceId,
            package: packageId,
            date: bookingDate
        });

        isFetchingSlots = true;

        try {
            const response = await fetch('/booking/slots', {
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
                let errMsg = 'Gagal fetch slot waktu.';
                try {
                    const errorData = await response.json();
                    errMsg = errorData?.error || errMsg;
                } catch (_) { }
                console.error('Error:', errMsg);
                populateTimeSlots({ error: errMsg });
                return;
            }

            const slots = await response.json();
            console.log("Slots dari server:", slots);

            populateTimeSlots(slots);

        } catch (err) {
            console.error('Network error:', err);
            populateTimeSlots({ error: 'Gagal konek ke server.' });
        } finally {
            isFetchingSlots = false;
        }
    }

    function populateTimeSlots(slots) {
        timeSlotSelect.innerHTML = '<option value="">Pilih waktu</option>';

        if (slots.error) {
            const opt = document.createElement('option');
            opt.textContent = slots.error;
            opt.disabled = true;
            timeSlotSelect.appendChild(opt);
            timeSlotSelect.disabled = true;
            return;
        }

        if (!Array.isArray(slots) || slots.length === 0) {
            const opt = document.createElement('option');
            opt.textContent = 'Slot kosong semua 😭';
            opt.disabled = true;
            timeSlotSelect.appendChild(opt);
            timeSlotSelect.disabled = true;
            return;
        }

        // Populate available slots
        slots.forEach(slot => {
            const opt = document.createElement('option');
            opt.value = slot.start; // e.g. "09:00"
            opt.textContent = `${slot.start} - ${slot.end}`; // e.g. "09:00 - 10:00"
            timeSlotSelect.appendChild(opt);
        });

        timeSlotSelect.disabled = false;
    }

    // Single comprehensive handler for all changes that affect slots
    function handleSlotDependencyChange(source = 'unknown') {
        console.log(`handleSlotDependencyChange called from: ${source}`);

        // Clear any existing timeout first
        clearTimeout(fetchSlotsTimeout);

        // Set new timeout for debounced execution
        fetchSlotsTimeout = setTimeout(() => {
            fetchAvailableSlots();
        }, 300);
    }

    // Event listeners - consolidated to prevent multiple calls
    if (serviceSelect) {
        serviceSelect.addEventListener('change', function () {
            console.log('Service changed, loading packages...');
            loadPackages(); // This will also call updatePrice internally
            handleSlotDependencyChange('service-change');
        });
    }

    if (packageSelect) {
        packageSelect.addEventListener('change', function () {
            console.log('Package changed, updating price...');
            updatePrice();
            handleSlotDependencyChange('package-change');
        });
    }

    if (bookingDateInput) {
        bookingDateInput.addEventListener('change', function () {
            console.log('Date changed...');
            handleSlotDependencyChange('date-change');
        });
    }

    // Payment radio buttons
    document.querySelectorAll('input[name="payment"]').forEach(radio => {
        radio.addEventListener('change', toggleDpInfo);
    });

    function showConfirmModal() {
        return new Promise((resolve) => {
            const modal = document.getElementById('confirmModal');
            const yesBtn = document.getElementById('confirmYes');
            const noBtn = document.getElementById('confirmNo');

            // Show modal
            modal.classList.remove('hidden');

            function cleanUp() {
                modal.classList.add('hidden');
                yesBtn.removeEventListener('click', onYes);
                noBtn.removeEventListener('click', onNo);
            }

            function onYes() {
                cleanUp();
                resolve(true);
            }

            function onNo() {
                cleanUp();
                resolve(false);
            }

            yesBtn.addEventListener('click', onYes);
            noBtn.addEventListener('click', onNo);
        });
    }



    // Form submission validation
    if (bookingForm) {
        bookingForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent page reload

            showConfirmModal().then(confirmed => {
                if (!confirmed) {
                    // User batal submit, langsung return
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerText = 'Processing...';

                const formData = new FormData(bookingForm);
                const formAction = bookingForm.getAttribute('data-url');

                fetch(formAction, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData
                })
                    .then(async response => {
                        const text = await response.text();
                        console.log('RAW response:', text);

                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (err) {
                            console.error('Gagal parse JSON:', err);
                            throw new Error('Response bukan JSON valid');
                        }

                        if (!response.ok) {
                            console.error("Error Response:", data);

                            if (data.message) {
                                alert(data.message);
                            } else if (data.errors) {
                                const firstError = Object.values(data.errors)[0];
                                alert(Array.isArray(firstError) ? firstError[0] : firstError);
                            } else {
                                alert("Gagal booking. Cek inputmu!");
                            }

                            if (data.errors) {
                                console.log("Validation errors:", data.errors);
                            }

                            throw new Error(`HTTP ${response.status}: ${data.message || 'Request failed'}`);
                        }

                        return data;
                    })
                    .then(data => {
                        console.log("Booking sukses:", data);

                        if (data.message) {
                            alert(data.message);
                        } else {
                            alert('Booking sukses!');
                        }

                        if (data.data && data.data.redirect_url) {
                            window.location.href = data.data.redirect_url;
                        } else {
                            window.location.href = `/booking`;
                        }
                    })
                    .catch(err => {
                        console.error("Booking gagal:", err);

                        if (!err.message.includes('HTTP')) {
                            alert("Terjadi error saat booking. Silakan coba lagi.");
                        }
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'Submit';
                    });
            });
        });
    }


    // Initialize on page load
    console.log('Initializing booking slide...');
    if (serviceSelect && serviceSelect.value) {
        console.log('Service pre-selected, loading packages...');
        loadPackages();
    }

    // Handle old input values - separate initialization
    if (packageSelect && packageSelect.value) {
        console.log('Package pre-selected, updating price...');
        updatePrice();
    }

    // Only fetch slots if ALL required fields are pre-filled
    if (serviceSelect?.value && packageSelect?.value && bookingDateInput?.value) {
        console.log('All fields pre-filled, fetching slots...');
        handleSlotDependencyChange('initialization');
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