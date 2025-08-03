// Зареждане на съобщения
function loadMessages() {
    if (!currentChatId) return;
    
    fetch(`get_messages.php?with=${currentChatId}&job=${currentJobId}`)
        .then(res => res.json())
        .then(messages => {
            const container = document.getElementById('chat-messages');
            container.innerHTML = '';
            
            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message');
                messageDiv.classList.add(msg.sender_id == currentUserId ? 'me' : 'you');
                
                messageDiv.innerHTML = `
                    <div class="message-content">${escapeHtml(msg.message)}</div>
                    <div class="message-time">${formatTime(msg.created_at)}</div>
                `;
                
                container.appendChild(messageDiv);
            });
            
            container.scrollTop = container.scrollHeight;
            
            // Маркираме съобщенията като прочетени
            if (messages.length > 0 && messages[messages.length - 1].sender_id != currentUserId) {
                markMessagesAsRead(currentChatId);
                fetch('update_messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    with_id: currentChatId,
                    job_id: currentJobId
                })
            });
            }
        })
        .catch(error => console.error('Error loading messages:', error));
}

// Изпращане на съобщение
document.getElementById('chat-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const messageInput = form.message;
    const message = messageInput.value.trim();
    
    if (!message || !currentChatId) return;
    
    const formData = new FormData(form);
    
    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            messageInput.value = '';
            loadMessages();
        }
    })
    .catch(error => console.error('Error sending message:', error));
});

// Помощни функции
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function markMessagesAsRead(contactId) {
    fetch('mark_as_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ contact_id: contactId })
    })
    .catch(error => console.error('Error marking messages as read:', error));
}

// Обновяване на съобщенията всеки 2 секунди и при фокус на прозореца
setInterval(loadMessages, 2000);
window.addEventListener('focus', loadMessages);

// Първоначално зареждане
document.addEventListener('DOMContentLoaded', function() {
    loadMessages();
    
    // Ако имаме активен чат, фокусираме полето за съобщение
    if (currentChatId) {
        document.querySelector('#chat-form input[name="message"]')?.focus();
    }
});