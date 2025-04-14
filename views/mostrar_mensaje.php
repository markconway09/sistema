<div class="modal" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content text-bg-dark">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="messageModalLabel">Comunicaciones <i class="bi bi-megaphone" style="color: #25BED4;"></i></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="message-content">No hay mensajes nuevos</div>
            </div>
        </div>
    </div>
</div>
<?php
if(session_status() == PHP_SESSION_NONE) session_start();
?>
<script>
    function fetchMessage() {
        $.ajax({
            url: 'controller/ajax_query.php',
            type: 'GET',
            data: {
                call: 5
            },
            dataType: 'json',
            success: function(data) {
                const messageContent = document.getElementById('message-content');
                let messagesAppended = false; // Flag to track if any messages were appended

                if (data.length > 0) {
                    messageContent.innerHTML = ''; // Clear previous messages
                    let messageCount = 0;
                    data.forEach(item => {
                        if (item.mensaje) {
                            let usuarios = item.usuario.split(',');
                            if (usuarios.includes('<?php echo $_SESSION["login"]; ?>')) {
                                const messageElement = document.createElement('h4');
                                messageElement.classList.add('display-6');
                                messageElement.classList.add('fw-bold');
                                messageElement.classList.add('px-3');
                                messageElement.classList.add('py-5');
                                messageElement.innerText = item.mensaje;
                                if (messageCount != 0) messageContent.appendChild(document.createElement('hr'));
                                messageContent.appendChild(messageElement);
                                messagesAppended = true; // Set flag to true if a message is appended
                                messageCount++;
                            }
                        }
                    });
                    if (messagesAppended) {
                        $('#announcementsPill').html(messageCount);
                        $('#messageModal').modal('show');
                    } else {
                        messageContent.innerText = 'No hay mensajes nuevos';
                    }
                } else {
                    console.error('No messages found in response:', data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching message:', status, error);
            }
        });
    }
</script>