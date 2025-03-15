document.addEventListener('DOMContentLoaded', function() {
    const appointmentForm = document.getElementById('appointment-form');
    
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(event) {
            // Reset previous error messages
            const errorElements = document.querySelectorAll('.error-message');
            errorElements.forEach(element => {
                element.remove();
            });
            
            let hasError = false;
            
            // Validate program
            const program = document.getElementById('program');
            if (!program.value) {
                displayError(program, 'Please select a program');
                hasError = true;
            }
            
            // Validate name
            const name = document.getElementById('name');
            if (!name.value.trim()) {
                displayError(name, 'Name is required');
                hasError = true;
            }
            
            // Validate address
            const address = document.getElementById('address');
            if (!address.value.trim()) {
                displayError(address, 'Address is required');
                hasError = true;
            }
            
            // Validate contact
            const contact = document.getElementById('contact');
            if (!contact.value.trim()) {
                displayError(contact, 'Contact information is required');
                hasError = true;
            }
            
            // Validate appointment date
            const appointmentDate = document.getElementById('appointment_date');
            if (!appointmentDate.value) {
                displayError(appointmentDate, 'Please select a date and time');
                hasError = true;
            } else {
                // Check if date is in the past
                const selectedDate = new Date(appointmentDate.value);
                const now = new Date();
                if (selectedDate < now) {
                    displayError(appointmentDate, 'Appointment date cannot be in the past');
                    hasError = true;
                }
            }
            
            // Prevent form submission if there are errors
            if (hasError) {
                event.preventDefault();
            }
        });
    }
    
    // Function to display error message
    function displayError(element, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#d9534f';
        errorDiv.style.fontSize = '0.9rem';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        
        // Insert error message after the element
        element.parentNode.insertBefore(errorDiv, element.nextSibling);
        
        // Highlight the input
        element.style.borderColor = '#d9534f';
    }
});