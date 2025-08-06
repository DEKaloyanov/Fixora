$(document).ready(function () {
    const messagesContainer = $('#chat-messages');
    const form = $('#message-form');
    const receiverId = $('input[name="receiver_id"]').val();
    const jobId = $('input[name="job_id"]').val();

    function loadMessages() {
        $.ajax({
            url: 'php/get_messages.php',
            method: 'GET',
            data: {
                with: receiverId,
                job: jobId
            },
            success: function (data) {
                messagesContainer.html(data);
                messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
            }
        });
    }

    loadMessages();
    setInterval(loadMessages, 3000);

    form.on('submit', function (e) {
        e.preventDefault();
        const messageInput = $('input[name="message"]');
        const message = messageInput.val().trim();

        if (message === '') return;

        $.ajax({
            url: 'php/send_message.php',
            method: 'POST',
            data: {
                receiver_id: receiverId,
                job_id: jobId,
                message: message
            },
            success: function () {
                messageInput.val('');
                loadMessages();
            }
        });
    });
});
