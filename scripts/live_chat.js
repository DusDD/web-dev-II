function loadChats() {
    $.ajax({
        url: 'load_chats.php',
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

        // Load messages for selected chat
        loadMessages();
    });
}


function loadMessages() {
    const selected = $('.chat.selected');
    const chatId = selected.data("chat-id");
    const chatName = selected.text();

    if (chatId == null || chatName == null) {
        $(".chat-name").text("no chat selected");
        $(".message-container").html("");
        return;
    }

    $.ajax({
        url: 'load_messages.php',
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

    messages.forEach(msg => {
        let wrapper = document.createElement("div");
        wrapper.classList.add("message");
        wrapper.classList.add(msg["is_sender"] ? "sent" : "received");
        container.appendChild(wrapper);

        let text = document.createElement("p");
        text.innerText = msg["message"];
        wrapper.appendChild(text);
    });
}

function startChat(_ev) {
    const username = $(".new-chat__input").val().trim();
    if (username.length === 0) return;
    console.debug(`starting chat with ${username}`);
    $.ajax({
        url: "start_chat.php",
        type: "POST",
        data: {username},
        success: () => {
            console.debug(`created chat with ${username}`);
            loadChats();
        }
    });
}

function sendMessage(ev) {
    ev.preventDefault();
    const selectedChatId = $(".chat.selected").data("chat-id");
    const message = $('#new-message__input').val();

    if (!selectedChatId) {
        console.debug(`Error: Tried to send message, but no chat is selected!`);
        return;
    }

    $.ajax({
        url: 'send_message.php',
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
        url: "autocomplete_username.php",
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
    }, 5000);
});