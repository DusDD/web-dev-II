document.addEventListener('DOMContentLoaded', () => {
    const selectElement = document.getElementById('select');

    selectElement.addEventListener('change', (event) => {
        const selectedValue = event.target.value;
        document.body.className = ''; // Reset any existing classes on the body
        document.body.classList.add(selectedValue);

        // Update input and button elements as well
        const inputs = document.querySelectorAll('input');
        const buttons = document.querySelectorAll('button');
        
        inputs.forEach(input => {
            input.className = ''; // Reset any existing classes on the input
            input.classList.add(selectedValue);
        });

        buttons.forEach(button => {
            button.className = ''; // Reset any existing classes on the button
            button.classList.add(selectedValue);
        });
    });

    // Trigger change event to apply the initial class
    selectElement.dispatchEvent(new Event('change'));
});

