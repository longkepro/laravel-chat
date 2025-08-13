import './bootstrap.js';

window.Echo.channel('notifications')
    .listen('UserSessionChange', (event) => {
        console.log('Event received:', event);
});
