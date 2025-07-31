// Зареждане на съобщения
function loadMessages() {
    const receiverId = document.querySelector('input[name="receiver_id"]').value;

    fetch(`get_messages.php?with=${receiverId}`)
        .then(res => res.json())
        .then(messages => {
            const container = document.getElementById('chat-messages');
            container.innerHTML = '';
            messages.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('message');
                div.classList.add(msg.sender_id === currentUserId ? 'me' : 'you');
                div.textContent = msg.message;
                container.appendChild(div);
            });
            container.scrollTop = container.scrollHeight;
        });
}

// Изпращане на съобщение
document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    }).then(() => {
        form.message.value = '';
        loadMessages();
    });
});

// Текущият потребител (от PHP)


// Обновяване на всеки 3 секунди
setInterval(loadMessages, 3000);

// Първоначално зареждане
document.addEventListener('DOMContentLoaded', loadMessages);
