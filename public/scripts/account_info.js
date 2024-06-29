function changePassword() {
    const currentPassword = document.getElementById("current-password").value;
    const newPassword = document.getElementById("new-password").value;
    const confirmPassword = document.getElementById("confirm-password").value;

    if (newPassword !== confirmPassword) {
        alert("New password does not match confirmation password!");
        return;
    }

    if (newPassword.length < 8) {
        alert("New password must be at least 8 characters long!");
        return;
    }

    if (currentPassword.length === 0) {
        alert("Current password must not be empty!")
        return;
    }

    $.ajax({
        url: '/actions/change_password.php',
        type: 'POST',
        dataType: "json",
        data: {
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        },
        success: () => {
            console.log("password change was successful!");
        },
        error: (err) => {
            console.error(err);
            alert("Password change failed!");
        }
    });
}

$(document).ready(function () {
    let changePwdButton = document.getElementById("change-password-btn");
    changePwdButton.addEventListener("click", changePassword);
});
