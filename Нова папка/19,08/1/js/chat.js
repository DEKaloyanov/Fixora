// js/chat.js
$(document).ready(function () {
  const messagesContainer = $('#chat-messages');
  const form = $('#message-form');
  const receiverId = $('input[name="receiver_id"]').val();
  const jobId = $('input[name="job_id"]').val();

  function isNearBottom() {
    const el = messagesContainer[0];
    if (!el) return true;
    return el.scrollTop + el.clientHeight >= el.scrollHeight - 80;
  }

  function scrollToBottom(smooth = false) {
    const el = messagesContainer[0];
    if (!el) return;
    if (smooth && 'scrollTo' in el) {
      el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
    } else {
      messagesContainer.scrollTop(el.scrollHeight);
    }
  }

  function loadMessages() {
    const auto = isNearBottom();
    $.ajax({
      url: 'php/get_messages.php',
      method: 'GET',
      data: { with: receiverId, job: jobId },
      success: function (data) {
        messagesContainer.html(data);
        if (auto) scrollToBottom(false);
      }
    });
  }

  // First load + polling
  loadMessages();
  const poll = setInterval(loadMessages, 3000);

  form.on('submit', function (e) {
    e.preventDefault();
    const messageInput = $('input[name="message"]');
    const message = (messageInput.val() || '').trim();
    if (!message) return;

    $.ajax({
      url: 'php/send_message.php',
      method: 'POST',
      data: { receiver_id: receiverId, job_id: jobId, message },
      success: function () {
        messageInput.val('');
        loadMessages();
        // леко плавно до дъното след изпращане
        setTimeout(() => scrollToBottom(true), 50);
      }
    });
  });

  // Когато потребителят скролира нагоре – не насилваме автоскрол до дъното
  messagesContainer.on('scroll', function () {
    // по желание тук може да се покаже бутон „към най-новите“
  });
});
