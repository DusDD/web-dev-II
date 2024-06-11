function loadUsers() {
    $.ajax({
        url: '/actions/admin/list_users.php',
        type: 'GET',
        dataType: "json",
        success: buildUserTable
    });
}

function buildUserTable(users) {
    console.log("users data:");
    console.log(users);
    const usersTable = $("#users-table");
    users.forEach(user => {
        let row = document.createElement("tr");

        let idEl = document.createElement("td");
        idEl.innerText = user["id"];
        row.appendChild(idEl);

        let nameEl = document.createElement("td");
        nameEl.innerText = user["username"];
        row.appendChild(nameEl);

        let deleteBtn = document.createElement("button");
        deleteBtn.classList.add("delete", "btn");
        deleteBtn.innerText = "X";
        deleteBtn.addEventListener("click", (ev) => {
            $.ajax({
                url: '/actions/admin/delete_user.php',
                type: 'POST',
                data: {user_id: user["id"]},
                dataType: "json",
                success: deleteUserPopup,
                error: console.error
            });
        })
        row.appendChild(deleteBtn);

        let adminBtn = document.createElement("button");
        adminBtn.classList.add("admin", "btn");
        // TODO: add make admin functionality
        row.appendChild(adminBtn);

        usersTable.append(row);
    })
}

function deleteUserPopup(metrics) {
    console.log(metrics);
    let popup = $("<div>", {
        title: "Deletion Metrics",
        html: `User deleted: ${metrics["is_user_deleted"]}<br>
Chats removed: ${metrics["deleted_chats"]}<br>
Mappings removed: ${metrics["deleted_mappings"]}<br>
Messaged removed: ${metrics["deleted_messages"]}<br>`
    });
    popup.appendTo("body");
    popup.dialog();
}

$(document).ready(function () {
    loadUsers();
});
