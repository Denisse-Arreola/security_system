// JavaScript source code


function login(event) {
    let number = event.target.txtEmployee.value;
    let password = event.target.txtPassword.value;
    let danger = document.getElementById("danger");

    let params = new URLSearchParams(
        {
            "session_action": "start_session",
            "number": number,
            "password": password

        }
    ).toString();

    // console.log("http://localhost/IoT_projects/security_system/src/api.php?" + params);

    fetch("http://localhost/IoT_projects/security_system/src/api.php?" + params).
        then(res => res.json()).
        then(
            data => {
                let status = data.status;
                if (status) {
                    location.replace("http://localhost/IoT_projects/security_system/");
                } else {
                    danger.style.display = "block";
                }
            }
        )

    event.preventDefault();
}


function logup(event) {
    let firstname = event.target.txtName.value;
    let lastname = event.target.txtLast.value;
    let number = event.target.txtNum.value;
    let password = event.target.txtPass.value;
    let key = event.target.txtKey.value;

    let params = new URLSearchParams(
        {
            "session_action": "new_user",
            "firstname": firstname.toUpperCase(),
            "lastname": lastname.toUpperCase(),
            "number": number,
            "password": password,
            "key": key
        }
    ).toString();

    console.log("http://localhost/IoT_projects/security_system/src/api.php?" + params);

    fetch("http://localhost/IoT_projects/security_system/src/api.php?" + params).
        then(res => res.json()).
        then(
            data => {
                let status = data.status;
                if (status) {
                    location.replace("http://localhost/IoT_projects/security_system/");
                } else {
                    danger.style.display = "block";
                }
            }
        )

    event.preventDefault();
}


