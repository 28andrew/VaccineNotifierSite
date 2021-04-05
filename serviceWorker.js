self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const sendNotification = body => {
        var json = JSON.parse(body);
        return self.registration.showNotification(json.title, {
            body: json.body,
            tag: json.tag,
            data: {
                url: json.url
            }
        });
    };

    if (event.data) {
        const message = event.data.text();
        event.waitUntil(sendNotification(message));
    }
});

self.addEventListener('notificationclick', function(event) {
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
})