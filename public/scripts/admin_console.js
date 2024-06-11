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

    // clear existing user rows
    $(".user-row").remove();

    // fill the users table with the supplied users data
    const usersTable = $("#users-table");
    users.forEach(user => {
        let row = document.createElement("tr");
        row.classList.add("user-row");

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
                data: {user_id: user["id"], make_admin: user["is_admin"] ? 0 : 1},
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

function exportData() {
    const dataArea = $("#dataExportArea");
    $.ajax({
        url: '/actions/admin/export_data.php',
        type: 'POST',
        dataType: "json",
        success: (data) => {
            console.log("export data:");
            console.log(data);
            dataArea.val(JSON.stringify(data, null, 4));
        },
        error: console.error
    });
}

$(document).ready(function () {
    // Load users data and fill the users table
    loadUsers();

    // Add data export functionality
    const exportButton = document.getElementById("dataExportButton");
    exportButton.addEventListener("click", exportData);
});
