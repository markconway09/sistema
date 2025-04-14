function fetchData() {
        fetch('controller/notificar/poll.php')
            .then(response => response.json())
            .then(data => {
                // Check if there's a notification
                if (data.notification) {
                    showToast(data.notification);
                }
            })
            .catch(error => console.info('Error fetching data:', error));
    }
    function showToast(message) {
        var toastContainer = document.getElementById('toastContainer');
    
        // Ensure the container exists, if not, create it.
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.classList.add('position-fixed', 'bottom-0', 'end-0', 'p-3');
            toastContainer.style.zIndex = '1055'; // Ensure it's on top of other elements
            document.body.appendChild(toastContainer);
        }
    
        // Create a new toast element
        var toastDiv = document.createElement('div');
        toastDiv.innerHTML = `
            <div class="toast align-items-center text-bg-danger border-0 my-1" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
    
        // Append the toast to the container
        toastContainer.appendChild(toastDiv);
    
        // Initialize and show the toast
        var toast = new bootstrap.Toast(toastDiv.querySelector('.toast'), { autohide: false });
        toast.show();
    
        // Optional: Remove the toast from the DOM once it is hidden
        toastDiv.querySelector('.toast').addEventListener('hidden.bs.toast', function () {
            toastDiv.remove();
        });
    }
    
    // Poll the server every 5 seconds
    setInterval(fetchData, 5000);