document.addEventListener('DOMContentLoaded', () => {
    // El formulario contiene ambos métodos; solo se habilitan los campos del método elegido.
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const cardFields = document.querySelector('#card-fields');
    const paypalFields = document.querySelector('#paypal-fields');

    const setPaymentFields = () => {
        if (!cardFields || !paypalFields) {
            return;
        }

        const method = document.querySelector('input[name="payment_method"]:checked')?.value ?? 'card';
        const usesCard = method === 'card';

        cardFields.classList.toggle('d-none', !usesCard);
        paypalFields.classList.toggle('d-none', usesCard);

        // required se sincroniza para que la validación del navegador no bloquee campos ocultos.
        cardFields.querySelectorAll('input').forEach((input) => input.required = usesCard);
        paypalFields.querySelectorAll('input').forEach((input) => input.required = !usesCard);
    };

    paymentRadios.forEach((radio) => radio.addEventListener('change', setPaymentFields));
    setPaymentFields();

    // La máscara mejora lectura; el servidor elimina espacios antes de validar.
    const cardNumber = document.querySelector('#card_number');
    cardNumber?.addEventListener('input', () => {
        cardNumber.value = cardNumber.value.replace(/\D/g, '').slice(0, 19).replace(/(.{4})/g, '$1 ').trim();
    });

    // Inserta automáticamente la barra del formato MM/AA.
    const expiry = document.querySelector('#card_expiry');
    expiry?.addEventListener('input', () => {
        const digits = expiry.value.replace(/\D/g, '').slice(0, 4);
        expiry.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
    });
});
