function getParameter(key) {
    const address = window.location.search;
    const parameterList = new URLSearchParams(address);
    return parameterList.get(key);
}

function showNotification(bool, text) {
    let notification = document.createElement('div');
    notification.innerHTML = `<p>${text}</p><button type="button" class="cancel-btn" onclick='hideNotification(this.parentElement);'>✕</button>`;
    notification.classList.add('notification');
    if (bool) notification.classList.add('success');
    else notification.classList.add('failure');
    document.body.appendChild(notification);
}

function hideNotification(notification) {
    notification.style.opacity = 0;
    setTimeout(() => notification.remove(), 200);
    window.history.replaceState({}, '', "/");
}