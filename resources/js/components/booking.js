function initBookingSlide() {
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
            if (!element.value.trim()) {
                element.classList.remove('border-gray-300');
                element.classList.add('border-red-500');
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

    function submitForm() {
        if (!validateStep(3)) {
            return;
        }

        // Collect all form data
        const formData = {
            full_name: document.getElementById('full_name').value,
            email: document.getElementById('email').value,
            phone_number: document.getElementById('phone_number').value,
            service: document.getElementById('service').value,
            package: document.getElementById('package').value,
            payment: document.querySelector('input[name="payment"]:checked')?.value,
            preferred_date: document.getElementById('preferred_date').value,
            preferred_time: document.getElementById('preferred_time').value,
            notes: document.getElementById('notes').value
        };

        console.log('Form Data:', formData);
        alert('Form submitted successfully! Check console for data.');
    }

    // Expose functions ke global scope agar bisa dipanggil dari HTML
    window.nextStep = nextStep;
    window.submitForm = submitForm;
    window.validateStep = validateStep;
}

// Auto-initialize ketika script dimuat
document.addEventListener('DOMContentLoaded', initBookingSlide);