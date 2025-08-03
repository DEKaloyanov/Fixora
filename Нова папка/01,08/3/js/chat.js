

document.addEventListener('DOMContentLoaded', function() {
    // Елементи от DOM
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = messageForm ? messageForm.querySelector('textarea[name="message"]') : null;
    
    // Проверка дали има активен чат
    if (!messageForm) return;
    
    const receiverId = messageForm.querySelector('input[name="receiver_id"]').value;
    const jobId = messageForm.querySelector('input[name="job_id"]').value;
    
    // Променливи за управление на състоянието
    let isSending = false;
    let lastMessageId = 0;
    let refreshInterval;
    
    // Функция за зареждане на съобщения
    function loadMessages() {
        fetch(`get_messages.php?receiver_id=${receiverId}&job_id=${jobId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                
                // Проверка за нови съобщения
                if (data.messages.length > 0) {
                    const newLastMessageId = data.messages[data.messages.length - 1].id;
                    
                    if (newLastMessageId !== lastMessageId) {
                        renderMessages(data.messages);
                        lastMessageId = newLastMessageId;
                        scrollToBottom();
                    }
                }
            })
            .catch(error => console.error('Грешка при зареждане на съобщения:', error));
    }
    
    // Функция за визуализиране на съобщения
    function renderMessages(messages) {
        messagesContainer.innerHTML = '';
        
        messages.forEach(message => {
            const messageElement = document.createElement('div');
            messageElement.className = `message ${message.is_current_user ? 'outgoing' : 'incoming'}`;
            
            messageElement.innerHTML = `
                <div class="message-header">
                    <img src="${message.profile_image}" alt="${message.sender_name}">
                    <span class="sender-name">${message.sender_name}</span>
                    <span class="message-time">${message.created_at}</span>
                </div>
                <div class="message-content">${message.message}</div>
            `;
            
            messagesContainer.appendChild(messageElement);
        });
    }
    
    // Функция за автоматично скролиране към последното съобщение
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Функция за изпращане на съобщение
    function sendMessage(e) {
        e.preventDefault();
        
        if (isSending || !messageInput.value.trim()) return;
        
        isSending = true;
        const formData = new FormData(messageForm);
        
        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Добавяне на новото съобщение към чата
                const messages = [data.message];
                renderMessages(messages);
                scrollToBottom();
                
                // Изчистване на полето за въвеждане
                messageInput.value = '';
            } else {
                alert(data.error || 'Грешка при изпращане на съобщението');
            }
        })
        .catch(error => {
            console.error('Грешка при изпращане:', error);
            alert('Възникна грешка при изпращане на съобщението');
        })
        .finally(() => {
            isSending = false;
        });
    }
    
    // Слушатели за събития
    if (messageForm) {
        messageForm.addEventListener('submit', sendMessage);
    }
    
    // Автоматично обновяване на съобщенията
    refreshInterval = setInterval(loadMessages, 3000);
    
    // Първоначално зареждане на съобщения
    loadMessages();
    
    // Спиране на интервала при излизане от страницата
    window.addEventListener('beforeunload', () => {
        clearInterval(refreshInterval);
    });
});