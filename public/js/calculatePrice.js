addEventListener("DOMContentLoaded", () => {
    function calculateFrontendTotal() {
        const selectedStand = document.getElementById('stand_type_id');
        const selectedStandPrice = parseFloat(selectedStand.options[selectedStand.selectedIndex].dataset.price || 0);

        const selectedStandPriceOutput = document.getElementById('stand_type_price');
        selectedStandPriceOutput.textContent = selectedStandPrice.toFixed(2).replace('.', ',') + ' €';

        let optionTotal = 0;
        document.querySelectorAll('input[name="booking_options[]"]:checked').forEach(opt => {
            optionTotal += parseFloat(opt.dataset.price || 0);
        });


        const optionTotalOutput = document.getElementById('booking_options_price');
        optionTotalOutput.textContent = optionTotal.toFixed(2).replace('.', ',') + ' €';

        const total = selectedStandPrice + optionTotal;

        const totalOutput = document.getElementById('total-price');
        totalOutput.textContent = total.toFixed(2).replace('.', ',') + ' €';

        return total;
    }

    calculateFrontendTotal();
    // Wenn sich Auswahl ändert → Summe neu berechnen
    document.getElementById('stand_type_id').addEventListener('change', calculateFrontendTotal);
    document.querySelectorAll('input[name="booking_options[]"]').forEach(cb => {
        cb.addEventListener('change', calculateFrontendTotal);
    });
});


