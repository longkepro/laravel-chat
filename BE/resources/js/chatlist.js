window.Echo.channel('chat')
    .listen('MessageSent', (event) => {
        const userId = event.message.sender_id;
        const userCard = document.getElementById(userId);

        if (userCard) {
            // update last message
            const lastMsgSpan = userCard.querySelector('.last-message');
            if (lastMsgSpan) {
                lastMsgSpan.textContent = event.message.message;
                lastMsgSpan.classList.add('font-bold', 'text-black');
            }

            // thêm chấm xanh
            let dot = userCard.querySelector('.unread-dot');
            if (!dot) {
                dot = document.createElement('span');
                dot.className = 'unread-dot inline-block w-3 h-3 bg-green-500 rounded-full';
                userCard.querySelector('.w-full').appendChild(dot);
            }

            // di chuyển user lên đầu danh sách
            const parent = userCard.parentNode;
            parent.prepend(userCard);
        }
    });
