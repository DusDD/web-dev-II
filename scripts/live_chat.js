function loadChats() {
    $.ajax({
        url: 'load_chats.php',
        type: 'GET',
        success: updateChats
    });
}

function updateChats(newHtml) {
    // save current selected chat
    const selectedChatId = $("#chat-list .selected").attr("data-chat-id");

    // overwrite current html with the new data
    $('#chat-list').html(newHtml);

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
    let firstChat = $('#chat-list li:first');
    if (!hasSelection && firstChat.length !== 0) {
        // Load messages for the first chat by default
        firstChat.addClass("selected");
        loadMessages(firstChat.attr('data-chat-id'));
    }

    // make chats selectable by clicking
    addChatClickAction();
}

function addChatClickAction() {
    // Change chat selection when user clicks on a chat
    $("#chat-list .chat").on("click", ev => {
        // Deselect previously selected chat and select the clicked chat
        $("#chat-list .selected").removeClass("selected");
        ev.target.classList.add("selected");

        // Load messages for selected chat
        const chatId = ev.target.getAttribute('data-chat-id');
        loadMessages(chatId);
    });
}


function loadMessages(chatId) {
    $.ajax({
        url: 'load_messages.php',
        type: 'GET',
        data: {chat_id: chatId},
        success: function (data) {
            $('#chat-box').html(data);
        }
    });
}

function startChat(_ev) {
    const username = $("#new-chat-input").val().trim();
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
    const selectedChatId = $("#chat-list .selected").attr("data-chat-id");
    const message = $('#message-input').val();

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
            $('#message-input').val('');
            // reload messages
            loadMessages($('#chat-list .selected').attr('data-chat-id'));
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
    $("#new-chat-button").on("click", startChat);

    // Send message if user submits a new message
    $('#message-form').submit(sendMessage);

    // Completion functionality on user search
    $("#new-chat-input").autocomplete({
        source: usernameCompletion,
        minLength: 2 // Minimum characters before autocomplete starts
    });

    // Refresh messages every 5 seconds
    setInterval(function () {
        loadChats();
        loadMessages($('#chat-list .selected').attr('data-chat-id'));
    }, 5000);
});