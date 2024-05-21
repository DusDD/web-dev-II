document.addEventListener('DOMContentLoaded', () => {
    const selectElement = document.getElementById('select');
    const blueLogo = document.getElementById('blue');
    const yellowLogo = document.getElementById('yellow');

    const updateLogo = (selectedValue) => {
        if (selectedValue === 'blue') {
            blueLogo.style.display = 'block';
            yellowLogo.style.display = 'none';
        } else if (selectedValue === 'yellow') {
            blueLogo.style.display = 'none';
            yellowLogo.style.display = 'block';
        }
    };

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

        // Update the logo based on the selected value
        updateLogo(selectedValue);
    });

    // Trigger change event to apply the initial class and display the initial logo
    selectElement.dispatchEvent(new Event('change'));
});



