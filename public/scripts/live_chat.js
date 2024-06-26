function loadChats() {
    $.ajax({
        url: '/actions/load_chats.php',
        type: 'GET',
        dataType: "json",
        success: updateChatElements
    });
}

function updateChatElements(chats) {
    // save current selected chat id, so we can reselect it later
    const selectedChatId =  $(".chat-list .selected").data("chat-id");

    // get list of provided chat ids
    let updateChatIds = [];
    chats.forEach(chat => updateChatIds.push(chat["chat_id"]));

    // check if the chat elements must be updated;
    // if we have exactly the same chats in the same order, skip the update
    let hasDifference = false;

    // get list of displayed chat ids
    let existingChatIds = [];
    $(".chat").each(i => {
        let chatId = $(this).data("chat-id");
        existingChatIds.push(chatId);

        if (i >= updateChatIds.length || updateChatIds[i] !== chatId) {
            hasDifference = true;
        }
    });

    if (updateChatIds.length !== existingChatIds.length)
        hasDifference=true;


    if (!hasDifference) {
        // no need for update
        // TODO: update last active time
        return;
    }

    // clear existing chats
    const chatList = $(".chat-list");
    chatList.html("");

    chats.forEach(chat => {
        let chatId = chat["chat_id"];
        let newChat = $("<li>", {
            class: "chat",
            text: chat["name"]
        });
        newChat.data("chat-id", chatId);
        // TODO: remove data- attribute
        newChat.attr("data-chat-id", chatId);
        chatList.append(newChat);

        if (chatId === selectedChatId) {
            newChat.addClass("selected");
        }

    });

    // if no chat is/was selected, select the first if it exists
    let hasSelection = $(".chat.selected").length > 0;
    let firstChat = $('.chat').first();
    if (!hasSelection && firstChat.length !== 0) {
        // Select the first chat by default
        firstChat.addClass("selected");
    }

    // make chats selectable by clicking
    addChatClickAction();
}

function addChatClickAction() {
    // Change chat selection when user clicks on a chat
    $(".chat").on("click", ev => {
        // Deselect previously selected chat and select the clicked chat
        $(".chat.selected").removeClass("selected");
        ev.target.classList.add("selected");

        // Set chat id in send message element
        $(".new-message #chat_id").attr("value", $(".chat.selected").data("chat-id"));


        // Load messages for selected chat
        loadMessages();
    });
}


function loadMessages() {
    const selected = $('.chat.selected');
    // const chatId = selected.attr("data-chat-id");
    const chatId = selected.data("chat-id");
    const chatName = selected.text();

    if (chatId == null || chatName == null) {
        $(".chat-name").text("no chat selected");
        $(".message-container").html("");
        return;
    }

    $.ajax({
        url: '/actions/load_messages.php',
        type: 'GET',
        dataType: "json",
        data: {chat_id: chatId},
        success: data => {
            $(".chat-name").text(chatName);
            buildMessageElements(data);
        }
    });
}

function buildMessageElements(messages) {
    console.debug(`messages=${messages}`);
    const container = document.querySelector(".message-container");
    container.innerHTML = "";

    let lastDate = null;
    messages.forEach(msg => {
        let wrapper = document.createElement("div");
        wrapper.classList.add("message");
        wrapper.classList.add(msg["is_sender"] ? "sent" : "received");

        let curDate = new Date(msg["date"]);
        // check this message was sent on a 'new' date (i.e. the last message wasn't sent on the same day)
        if (lastDate == null
            || curDate.getFullYear() !== lastDate.getFullYear()
            || curDate.getMonth() !== lastDate.getMonth()
            || curDate.getDay() !== lastDate.getDay()) {
            // display the date of the message in a new element
            let dateElement = document.createElement("div");
            dateElement.innerText = curDate.toDateString();
            container.appendChild(dateElement);
        }

        let msgText = document.createElement("p");
        msgText.classList.add("message-text");
        msgText.innerText = msg["message"];
        wrapper.appendChild(msgText);

        let msgTime = document.createElement("span");
        msgTime.classList.add("message-time");
        msgTime.innerText = `${curDate.getHours()}:${curDate.getMinutes()}`;
        wrapper.appendChild(msgTime)

        if (msg["is_sender"]) {
            let deleteBtn = document.createElement("button");
            deleteBtn.classList.add("msg-delete-button");
            deleteBtn.innerText = "X";
            deleteBtn.addEventListener("click", (_ev) => {
                $.ajax({
                    url: '/actions/delete_message.php',
                    type: 'POST',
                    dataType: "json",
                    data: {message_id: msg["id"]},
                    success: (deleted_rows) => {
                        if (deleted_rows === 1) {
                            console.log(`Message ${msg["id"]} was removed`);
                            wrapper.remove();
                        } else {
                            console.error(`Error removing message ${msg["id"]}: ${deleted_rows} rows were deleted`);
                        }
                    },
                    error: console.error
                });
            });
            wrapper.appendChild(deleteBtn);
        }

        /*
        let deleteImg = document.createElement("img");
        deleteImg.setAttribute("src", "/images/icons8-delete-96.png")
        deleteImg.setAttribute("alt", "Delete");
        deleteBtn.appendChild(deleteImg);
         */

        container.appendChild(wrapper);
        lastDate = curDate;
    });
}

function startChat(ev) {
    ev.preventDefault();
    
    const username = $("#new-chat__input").val().trim();
    if (username.length === 0) return;
    console.debug(`starting chat with ${username}`);
    $.ajax({
        url: "/actions/start_chat.php",
        type: "POST",
        data: {username},
        success: function (data) {
            console.debug(`created chat with ${username}`);
            loadChats();
        }
    });
}

function sendMessage(ev) {
    ev.preventDefault();
    const selectedChatId = $(".chat.selected").data("chat-id");

    // Check if selectedChatId is undefined or null
    if (selectedChatId === undefined || selectedChatId === null) {
        console.debug(`Error: Tried to send message, but no chat is selected!`);
        return;
    }

    const message = $('#new-message__input').val();

    $.ajax({
        url: '/actions/send_message.php',
        type: 'POST',
        data: {chat_id: parseInt(selectedChatId), message},
        success: function () {
            // clear message field
            $('#new-message__input').val('');
            // reload messages
            loadMessages();
        }
    });
}

function usernameCompletion(request, response) {
    $.ajax({
        url: "/actions/autocomplete_username.php",
        dataType: "json",
        data: {
            search_string: request.term
        },
        success: function (data) {
            response(data);
        }
    });
}

$(document).ready(function () {
    // Load chats on page load
    loadChats();

    // Make new chat button create a new chat
    $("#new-chat__button").on("click", startChat);

    // Send message if user submits a new message
    $('#new-message__form').submit(sendMessage);

    // Completion functionality on user search
    $("#new-chat__input").autocomplete({
        source: usernameCompletion,
        minLength: 2 // Minimum characters before autocomplete starts
    });

    // Refresh messages every 5 seconds
    setInterval(function () {
        loadChats();
        loadMessages();
    }, 1*1000);
});