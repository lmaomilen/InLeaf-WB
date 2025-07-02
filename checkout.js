function validateForm() {
    const addressType = document.querySelector('input[name="address_type"]:checked').value;
    const paymentMethod = document.getElementById('payment-method').value;
    const cardNumber = document.getElementById('card_number')?.value.replace(/\s/g, '') ?? '';
    const expiry = document.getElementById('card_expiry')?.value ?? '';
    const cvc = document.querySelector('[name="card_cvc"]')?.value ?? '';
    const paypalEmail = document.querySelector('[name="paypal_email"]')?.value ?? '';

    let isValid = true;

    if (addressType === 'new') {
        const street = document.querySelector('input[name="street"]').value.trim();
        const postal = document.querySelector('input[name="postal_code"]').value.trim();
        const country = document.querySelector('input[name="country"]').value.trim();
        if (!street || !postal || !country) {
            alert("Please fill in all address fields.");
            isValid = false;
        }
    } else {
        const savedAddress = document.querySelector('select[name="saved_address_id"]').value;
        if (!savedAddress) {
            alert("Please select a saved address.");
            isValid = false;
        }
    }

    if (!paymentMethod) {
        alert("Please select a payment method.");
        isValid = false;
    } else if (paymentMethod === 'card') {
        const [month] = expiry.split('/');
        if (!cardNumber || cardNumber.length !== 16 || !expiry || parseInt(month) > 12 || !cvc) {
            alert("Fill all valid card details.");
            isValid = false;
        }
    } else if (paymentMethod === 'paypal') {
        if (!paypalEmail.includes('@')) {
            alert("Enter a valid PayPal email.");
            isValid = false;
        }
    }

    return isValid;
}

    let currentStep = 1;

    function showStep(step) {
        document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        document.getElementById('progress-bar').style.width = step === 2 ? '100%' : '50%';
        document.getElementById('progress-text').innerText = 'Step ' + step + ' of 2';
        document.getElementById('next-btn').classList.toggle('hidden', step === 2);
        document.querySelector('button[type="submit"]').classList.toggle('hidden', step !== 2);

    }

    function nextStep() {
        if (currentStep < 2) currentStep++;
        showStep(currentStep);
    }

    function prevStep() {
        if (currentStep > 1) currentStep--;
        showStep(currentStep);
    }

    document.querySelectorAll('input[name="address_type"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const isNew = document.querySelector('input[name="address_type"]:checked').value === 'new';
            document.getElementById('new-address-fields').classList.toggle('hidden', !isNew);
            document.getElementById('saved-address-fields').classList.toggle('hidden', isNew);
        });
    });

    document.getElementById('payment-method').addEventListener('change', () => {
        const val = document.getElementById('payment-method').value;
        document.getElementById('card-fields').classList.toggle('hidden', val !== 'card');
        document.getElementById('paypal-fields').classList.toggle('hidden', val !== 'paypal');
    });

    // Инициализация
    showStep(currentStep);
    function checkAddressValidity() {
    const addressType = document.querySelector('input[name="address_type"]:checked').value;
    const nextBtn = document.getElementById('next-btn');

    if (addressType === 'new') {
        const street = document.querySelector('input[name="street"]').value.trim();
        const postal = document.querySelector('input[name="postal_code"]').value.trim();
        const country = document.querySelector('input[name="country"]').value.trim();

        nextBtn.disabled = !(street && postal && country);
    } else {
        const savedAddress = document.querySelector('select[name="saved_address_id"]').value;
        nextBtn.disabled = !savedAddress;
    }
}
checkAddressValidity();

document.querySelectorAll('input[name="address_type"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const isNew = radio.value === 'new';
        document.getElementById('new-address-fields').classList.toggle('hidden', !isNew);
        document.getElementById('saved-address-fields').classList.toggle('hidden', isNew);
        checkAddressValidity();
    });
});


['street', 'postal_code', 'country'].forEach(name => {
    const input = document.querySelector(`input[name="${name}"]`);
    if (input) {
        input.addEventListener('input', checkAddressValidity);
    };
});


const savedSelect = document.querySelector('select[name="saved_address_id"]');
if (savedSelect) {
    savedSelect.addEventListener('change', checkAddressValidity);
}

document.getElementById('payment-method').addEventListener('change', () => {
    const val = document.getElementById('payment-method').value;

    const cardFields = document.getElementById('card-fields');
    const paypalFields = document.getElementById('paypal-fields');

    cardFields.classList.toggle('hidden', val !== 'card');
    paypalFields.classList.toggle('hidden', val !== 'paypal');

   
    document.querySelector('[name="card_number"]').required = (val === 'card');
    document.querySelector('[name="card_expiry"]').required = (val === 'card');
    document.querySelector('[name="card_cvc"]').required = (val === 'card');
    document.querySelector('[name="paypal_email"]').required = (val === 'paypal');
    document.querySelector('[name="paypal_owner"]').required = (val === 'paypal');
});

document.addEventListener('DOMContentLoaded', () => {
    const expiryInput = document.getElementById('card_expiry');
    const dateInput = document.getElementById('delivery_date');
    const cardInput = document.getElementById('card_number');
    const form = document.getElementById('checkout-form');

    const expiryError = document.getElementById('expiry-error');
    const dateError = document.getElementById('date-error');
    const cardError = document.getElementById('card-error');

    
    if (expiryInput) {
    expiryInput.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '').substring(0, 4);
        this.value = value.length >= 3
        ? value.substring(0, 2) + '/' + value.substring(2)
        : value;

        expiryError?.classList.add('hidden');
    });

    expiryInput.addEventListener('blur', function () {
        const [month, year] = this.value.split('/');
        const isValidFormat = /^\d{2}\/\d{2}$/.test(this.value);
        if (!isValidFormat || parseInt(month) > 12) {
            expiryError.classList.remove('hidden');
        } else {
            expiryError.classList.add('hidden');
        }
        });

    }

    
    if (dateInput) {
        dateInput.addEventListener('blur', function () {
            const date = new Date(this.value);
            if (isNaN(date.getTime()) || date.getDate() > 31) {
                dateError.classList.remove('hidden');
            } else {
                dateError.classList.add('hidden');
            }
        });
    }

   
    if (cardInput) {
        cardInput.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '').substring(0, 16);
            let formatted = value.match(/.{1,4}/g)?.join(' ') ?? '';
            this.value = formatted;
            cardError?.classList.add('hidden');
        });
    }


   
    if (form) {
        form.addEventListener('submit', function (e) {
            const [month] = expiryInput?.value.split('/') ?? [];
            const date = dateInput ? new Date(dateInput.value) : null;
            const cardNumber = cardInput?.value.replace(/\s/g, '') ?? '';
            const paymentMethod = document.getElementById('payment-method').value;

            let hasError = false;

            
            if (dateInput && (isNaN(date.getTime()) || date.getDate() > 31)) {
                dateError?.classList.remove('hidden');
                hasError = true;
            } else {
                dateError?.classList.add('hidden');
            }

            
            if (paymentMethod === 'card') {
                if (!month || parseInt(month) > 12) {
                    expiryError?.classList.remove('hidden');
                    hasError = true;
                } else {
                    expiryError?.classList.add('hidden');
                }

                if (cardNumber.length !== 16) {
                    cardError?.classList.remove('hidden');
                    hasError = true;
                } else {
                    cardError?.classList.add('hidden');
                }
            }

            if (hasError) {
                e.preventDefault();
            }
        });
    }


});

document.getElementById('card_number')?.addEventListener('input', () => {
    document.getElementById('payment-method').value = 'card';
});

document.querySelector('[name="paypal_email"]')?.addEventListener('input', () => {
    document.getElementById('payment-method').value = 'paypal';
});



