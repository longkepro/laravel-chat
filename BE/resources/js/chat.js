import './bootstrap.js';
let currentPage = 1; // trang hiện tại
let loading = false;
const messagesElement = document.getElementById('messages');
let guestId = null; 
let lastSenderId = null;
const currentUserId = window.Laravel.userId; 
console.log("currentUserId:",currentUserId);
let receiverIdElement = null;


async function loadOlderMessages() {
    //container tạm thời chứa tin nhắn cũ
    const fragment = document.createDocumentFragment();

    // Giữ vị trí cuộn trước khi load
    const oldScrollHeight = messagesElement.scrollHeight;
    console.log(oldScrollHeight);

    //tránh việc load liên tục
    if(loading) return;
    loading = true;

    lastSenderId = guestId;//reset lastSenderId tránh trường hợp tin nhắn của khách là tin nhắn đầu tiên của đoạn chat mà không hiện avatar
    // Gọi API lấy tin nhắn cũ
    const response = await window.axios.get(`/dashboard/messages/${receiverIdElement}?page=${++currentPage}`);
    const messages = response.data;
    messages.forEach(msg => {
        if (msg.sender_id === currentUserId) {
            fragment.append(selfRender(msg.message));
        } else {
            fragment.append(guestRender(msg));
        }
    });
    messagesElement.prepend(fragment);
    messagesElement.scrollTop = messagesElement.scrollHeight - oldScrollHeight;
    loading = false;
    
}

async function loadMessages() {
  try {
    const response = await window.axios.get('/dashboard/messages/' + receiverIdElement);
    return response.data; // giờ thì return được ra ngoài
  } catch (error) {
    console.error(error);
    return []; // phòng khi lỗi vẫn return mảng rỗng
  }
}

//render tin nhắn của khách
function guestRender(e){
    const wrapper = document.createElement('div');
    const isNewSender = e.senderId !== lastSenderId;
    wrapper.className = "flex justify-start gap-2 items-end mb-1";//định dạng tin nhắn của khách

    // Nếu là khách và là tin nhắn mới đầu chuỗi -> hiển thị avatar
    if (isNewSender) {
        const avatar = document.createElement('img');
        avatar.src ="https://tiki.vn/blog/wp-content/uploads/2023/01/oLkoHpw9cqRtLPTbg67bgtUvUdV1BnXRnAqqBZOVkEtPgf-_Ct3ADFJYXIjfDd0fTyECLEsWq5yZ2CCOEGxIsuHSmNNNUZQcnQT5-Ld6yoK19Q_Sphb0MmX64ga-O_TIPjItNkTL5ns4zqP1Z0OBzsIoeYKtcewnrjnVsw8vfG8uYwwCDkXaoozCrmH1kA.jpg";
        avatar.className = "object-cover h-8 w-8 rounded-full";
        wrapper.appendChild(avatar);
    } else {
        const spacer = document.createElement('div');
        spacer.className = "w-8"; // chiều ngang = width avatar
        wrapper.appendChild(spacer);
    }

    const bubble = document.createElement('div');
    bubble.innerText = e.message;
    bubble.className = "bg-gray-200 px-3 py-2  max-w-xs rounded-bl-xl rounded-tr-2xl rounded-br-2xl break-words";

    wrapper.appendChild(bubble);
    lastSenderId = e.senderId;
    messagesElement.scrollTop = messagesElement.scrollHeight;
    return wrapper;
}

//render tin nhắn của bản thân user
function selfRender(messageValue){
    const wrapper = document.createElement('div');
    wrapper.className = "flex justify-end gap-2 items-end mb-1";

    const bubble = document.createElement('div');
    bubble.innerText = messageValue;
    bubble.className = "bg-blue-500 text-white px-3 py-2 max-w-xs rounded-br-xl rounded-tl-2xl rounded-bl-2xl break-words";
    
    wrapper.appendChild(bubble);
    lastSenderId = null;
    messagesElement.scrollTop = messagesElement.scrollHeight;
    return wrapper;
}


//chọn user để chat
document.querySelectorAll('.user-card').forEach(card => {
    card.addEventListener('click', () => {
        console.log("Click user id:", card.id);
        guestId = card.id;
        //đổi nền người đang chat
        if(receiverIdElement){
            document.getElementById(receiverIdElement).classList.remove('!bg-blue-100');//xóa nền xanh của user được chọn trước đó
        }
        card.classList.add('!bg-blue-100');//thêm nền xanh cho user được chọn
        receiverIdElement = card.id;//lưu id user được chọn
        console.log('avatar', card.querySelector('img').src);
        messagesElement.innerHTML = '';
        currentPage = 1;//reset trang về 1 khi chọn user

        (async () => {
            const messagesObject = await loadMessages();
            messagesObject.forEach(message => {
                if(message.sender_id === currentUserId) {
                    messagesElement.appendChild(selfRender(message.message));
                } else {
                    
                    messagesElement.appendChild(guestRender(message));
                }
            })
            //để bên dưới async thì sẽ chạy trước khi async thực hiện xong, nên để trong async
        })();
              
    });
});

// Lắng nghe sự kiện MessageSent từ kênh riêng tư dành cho người dùng hiện tại
Echo.private("chat." + currentUserId)
    .listen('.MessageSent', (e) => {
    console.log(e);
    if(!receiverIdElement || e.senderId != receiverIdElement) {
        // Nếu chưa chọn người nhận hoặc tin nhắn không phải từ người đang chat, bỏ qua
        return;
    }
    //chỉ listen tin nhắn của khách, nên khong cần check e.user.id !== currentUserId và không render tin nhắn cảu bản thân  
    messagesElement.appendChild(guestRender(e));
    messagesElement.scrollTop = messagesElement.scrollHeight;
});


// Gửi tin nhắn
const messageElement = document.getElementById('message')
const sendElement = document.getElementById('send')

sendElement.addEventListener('click', (e) => {
    e.preventDefault()

    if(receiverIdElement === null){
      return;
    }
   
    messagesElement.appendChild(selfRender(messageElement.value));
    messagesElement.scrollTop = messagesElement.scrollHeight;

    //gửi tin nhắn lên server để lưu vào db
    window.axios.post('/dashboard/message', {
        sender_id: currentUserId,
        message: messageElement.value,
        receiver_id: Number(receiverIdElement)
    })

    console.log(currentUserId,);
    console.log(messageElement.value);
    console.log(Number(receiverIdElement));
    messageElement.value = '';

});
// Lắng nghe sự kiện cuộn
messagesElement.addEventListener('scroll', () => {
    if (messagesElement.scrollTop === 0) {
        loadOlderMessages();
    }
});