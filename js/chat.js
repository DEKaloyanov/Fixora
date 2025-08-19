// js/chat.js
$(function () {
  const messagesContainer = $('#chat-messages');
  const form = $('#message-form');
  const receiverId = $('input[name="receiver_id"]').val();
  const jobId = $('input[name="job_id"]').val();
  const attachBtn = $('#attach-btn');
  const fileInput = $('#image-input');
  const previewWrap = $('#image-preview');
  const previewImg = $('#image-preview-img');
  const clearBtn = $('#clear-image');

  let selectedFile = null;

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
    $.get('php/get_messages.php', { with: receiverId, job: jobId }, function (data) {
      messagesContainer.html(data);
      if (auto) scrollToBottom(false);
    });
  }

  // First load + polling
  loadMessages();
  const poll = setInterval(loadMessages, 3000);

  // Attach flow
  attachBtn.on('click', () => fileInput.trigger('click'));

  fileInput.on('change', function () {
    const f = this.files && this.files[0];
    if (!f) return;
    if (!f.type.startsWith('image/')) {
      alert('Моля, изберете изображение.');
      this.value = '';
      return;
    }
    selectedFile = f;
    const url = URL.createObjectURL(f);
    previewImg.attr('src', url);
    previewWrap.addClass('is-visible'); // показва preview
  });

  clearBtn.on('click', function () {
    selectedFile = null;
    fileInput.val('');
    previewImg.attr('src', '');
    previewWrap.removeClass('is-visible'); // скрива preview
  });

  // Submit (text and/or image)
  form.on('submit', function (e) {
    e.preventDefault();
    const messageInput = $('input[name="message"]');
    const message = (messageInput.val() || '').trim();

    if (!message && !selectedFile) return;

    const fd = new FormData();
    fd.append('receiver_id', receiverId);
    fd.append('job_id', jobId);
    fd.append('message', message);
    if (selectedFile) fd.append('image', selectedFile);

    $.ajax({
      url: 'php/send_message.php',
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res && res !== 'ok') { alert(res); return; }
        messageInput.val('');
        clearBtn.click();
        loadMessages();
        setTimeout(() => scrollToBottom(true), 50);
      }
    });
  });

  // Optional: drop image to attach
  messagesContainer.on('dragover', e => { e.preventDefault(); });
  messagesContainer.on('drop', e => {
    e.preventDefault();
    const f = e.originalEvent.dataTransfer.files && e.originalEvent.dataTransfer.files[0];
    if (f && f.type.startsWith('image/')) {
      selectedFile = f;
      const url = URL.createObjectURL(f);
      previewImg.attr('src', url);
      previewWrap.addClass('is-visible'); // показва preview
    }
  });
});
