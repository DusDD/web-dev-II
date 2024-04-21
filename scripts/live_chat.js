function loadChats() {
    $.ajax({
        url: 'load_chats.php',
        type: 'GET',
        success: updateChats
    });
}

function updateChats(newHtml) {
    // save current selected chat
    const selectedChatId = $(".chat-list .selected").attr("data-chat-id");

    // overwrite current html with the new data
    $('.chat-list').html(newHtml);

    // reselect the chat that was previously selected
    let hasSelection = false;
    if (selectedChatId) {
        let newSelectedChat = $(`.chat[data-chat-id=${selectedChatId}]`);
        if (newSelectedChat.length !== 0) {
            newSelectedChat.addClass("selected");
            hasSelection = true;
        }
    }

    // if no chat is/was selected, select the first if it exists
    let firstChat = $('.chat-list li:first');
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
    const chatId = selected.attr("data-chat-id");
    const chatName = selected.text();

    if (chatId == null || chatName == null) {
        $(".chat-name").text("no chat selected");
        $(".message-container").html("");
        return;
    }

    $.ajax({
        dataType: "json",
        url: 'load_messages.php',
        type: 'GET',
        data: {chat_id: chatId},
        success: data => {
            $(".chat-name").text(chatName);
            buildMessages(data);
        }
    });
}

function buildMessages(messages) {
    console.debug(`messages=${messages}`);
    const container = document.querySelector(".message-container");
    container.innerHTML = "";

    messages.forEach(msg => {
        let wrapper = document.createElement("div");
        wrapper.classList.add("message");
        wrapper.classList.add(msg["isSender"] ? "sent" : "received");
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
        success: function (data) {
            console.debug(`created chat with ${username}`);
            loadChats();
        }
    });
}

function sendMessage(ev) {
    ev.preventDefault();
    const selectedChatId = $(".chat.selected").attr("data-chat-id");
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