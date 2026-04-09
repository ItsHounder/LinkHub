document.querySelectorAll('input[type="color"]').forEach(input => {
    const valueSpan = input.parentElement.querySelector('.color-value');
    
    input.addEventListener('input', function() {
        if (valueSpan) {
            valueSpan.textContent = this.value;
        }
        updatePreview();
    });
});

document.querySelectorAll('#display_name, #bio').forEach(input => {
    input.addEventListener('input', updatePreview);
});

function addLink() {
    const linksList = document.getElementById('linksList');
    const newLink = document.createElement('div');
    newLink.className = 'link-item';
    newLink.innerHTML = `
        <span class="drag-handle">☰</span>
        <input type="text" name="link_title[]" placeholder="Назва посилання">
        <input type="text" name="link_url[]" placeholder="https://">
        <input type="hidden" name="link_id[]" value="0">
        <button type="button" class="btn-delete" onclick="removeLink(this)">🗑</button>
    `;
    linksList.appendChild(newLink);

    newLink.querySelectorAll('input[type="text"]').forEach(input => {
        input.addEventListener('input', updatePreview);
    });
    
    updatePreview();
}

function removeLink(button) {
    if (confirm('Видалити це посилання?')) {
        button.parentElement.remove();
        updatePreview();
    }
}

function updatePreview() {
    const displayName = document.getElementById('display_name').value || 'Користувач';
    const bio = document.getElementById('bio').value || 'Твоя біографія з\'явиться тут';
    const bgColor1 = document.getElementById('bg_color1').value;
    const bgColor2 = document.getElementById('bg_color2').value;
    const buttonColor = document.getElementById('button_color').value;
    const buttonTextColor = document.getElementById('button_text_color').value;

    const previewPhone = document.getElementById('previewPhone');
    previewPhone.style.background = `linear-gradient(135deg, ${bgColor1} 0%, ${bgColor2} 100%)`;

    const avatar = document.getElementById('previewAvatar');
    avatar.textContent = displayName.charAt(0).toUpperCase();

    document.getElementById('previewUsername').textContent = displayName;
    document.getElementById('previewBio').textContent = bio;

    const previewLinks = document.getElementById('previewLinks');
    previewLinks.innerHTML = '';
    
    const linkItems = document.querySelectorAll('.link-item');
    linkItems.forEach(item => {
        const title = item.querySelector('input[name="link_title[]"]').value;
        const url = item.querySelector('input[name="link_url[]"]').value;
        
        if (title && url) {
            const linkEl = document.createElement('a');
            linkEl.className = 'preview-link';
            linkEl.href = '#';
            linkEl.textContent = title;
            linkEl.style.background = buttonColor;
            linkEl.style.color = buttonTextColor;
            linkEl.style.borderColor = buttonTextColor + '33';
            previewLinks.appendChild(linkEl);
        }
    });
}

document.getElementById('profileForm').addEventListener('submit', function(e) {
    const saveIndicator = document.getElementById('saveIndicator');
    saveIndicator.style.display = 'block';
    
    setTimeout(() => {
        saveIndicator.style.display = 'none';
    }, 3000);
});

updatePreview();
