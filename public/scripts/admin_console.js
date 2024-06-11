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
    usersTable.children(':gt(0)').remove();
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
        deleteBtn.addEventListener("click", (_ev) => {
            $.ajax({
                url: '/actions/admin/delete_user.php',
                type: 'POST',
                data: {user_id: user["id"]},
                dataType: "json",
                success: deleteUserPopup,
                error: console.error
            });
        })
        let deleteEl = document.createElement("td");
        deleteEl.appendChild(deleteBtn);
        row.appendChild(deleteEl);

        let adminBtn = document.createElement("button");
        adminBtn.classList.add("admin", "btn");
        if (user["is_admin"]) {
            adminBtn.innerText = "Revoke admin";
            adminBtn.classList.add("is_admin")
        } else {
            adminBtn.innerText = "Make admin";
            adminBtn.classList.add("is_user");
        }
        // TODO: add make admin functionality
        adminBtn.addEventListener("click", (_ev)=>{
            $.ajax({
                url: '/actions/admin/change_admin.php',
                type: 'POST',
                data: {user_id: user["id"], make_admin: !user["is_admin"]},
                dataType: "json",
                success: updatedAdminPopup,
                error: console.error
            });
        })
        let adminBtnEl = document.createElement("td");
        adminBtnEl.appendChild(adminBtn);
        row.appendChild(adminBtnEl);

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

    // reload users table
    setTimeout(loadUsers, 300);
}

function updatedAdminPopup(success) {
    console.log(`update admin success: ${success}`);
    let popup = $("<div>", {
        title: "Admin permission update",
        text: success ? "Success" : "Failure"
    });
    popup.appendTo("body");
    popup.dialog();

    // reload users table
    setTimeout(loadUsers, 300);
}

$(document).ready(function () {
    loadUsers();
});
