// JavaScript source code

function getData(lab, d) {
    let params = new URLSearchParams(
        {
            "system": "data",
            "num": lab,
            "d": d

        }
    ).toString();

    console.log("http://localhost/IoT_projects/security_system/src/api.php?" + params);

    fetch("http://localhost/IoT_projects/security_system/src/api.php?" + params).
        then(res => res.json()).
        then(
            data => {
                let status = data.status;
                if (status) {
                    // let test = document.getElementById("test");
                    // console.log(data);
                    // test.innerHTML = data.lab; 
                    extractData(data.data);
                } else {
                    console.log("Somthing bad");
                }
            }
        )

}

function extractData(array) {

    let arrayTemp = new Array();
    let arrayDate = new Array();
    let arrayHum = new Array();
    let arrayMot = new Array();
    let arrayAlarm = new Array();

    for (x = 0; x < array.length; x++) {
        let data = array[x];
        arrayTemp.push(data.temperature);
        arrayHum.push(data.humidity);
        arrayMot.push(parseInt(data.motion, 10));
        arrayDate.push(data.time);

        if (data.alarm != "0") {
            arrayAlarm.push(parseInt(data.motion, 10));
        } else {
            arrayAlarm.push(0);
        }

    }

    dashboard(arrayTemp, arrayDate, 'temperature', 'Temperature (°C)', 'Temperature');
    dashboard(arrayHum, arrayDate, 'humidity', 'Humidity (%)', 'Humidity');
    dashboard(arrayMot, arrayDate, 'motion', 'Motion', 'Motion');

}


function turn_alarm(lab, status) {

    let params = new URLSearchParams(
        {
            "system": "alarm",
            "num": lab,
            "on": status
        }
    ).toString();

    fetch("http://localhost/IoT_projects/security_system/src/api.php?" + params).
        then(res => res.json()).
        then(
            data => {
                let status = data.status;
                if (status) {
                    check_alarm(lab);
                } else {
                    console.log("Something wrong");
                    console.log(data.error);
                }
            }
        )
}


function check_alarm(lab) {

    let on = document.getElementById("on");
    let off = document.getElementById("off");
    let params = new URLSearchParams(
        {
            "system": "alarm",
            "num": lab
        }
    ).toString();

    fetch("http://localhost/IoT_projects/security_system/src/api.php?" + params).
        then(res => res.json()).
        then(
            data => {
                let status = data.status;
                if (status) {
                    if (data.alarm) {
                        on.disabled = true;
                        off.disabled = false;
                    } else {
                        on.disabled = false;
                        off.disabled = true;
                    }

                } else {
                    console.log("Something wrong");
                    console.log(data.error);
                    on.disabled = true;
                    off.disabled = true;
                }
            }
        )

}

function last_alarm(lab) {

    let params = new URLSearchParams(
        {
            "system": "alarm_motion",
            "num": lab
        }
    ).toString();

    console.log("http://localhost/IoT_projects/security_system/src/api.php?" + params);

    fetch("http://localhost/IoT_projects/security_system/src/api.php?" + params).
        then(res => res.json()).
        then(
            data => {
                let status = data.status;
                if (status) {
                    document.getElementById("alarm-data").innerHTML = data.time + " hrs</br></br>" + data.date;

                } else {
                    console.log("Something wrong");
                    console.log(data.error);
                    document.getElementById("alarm-data").innerHTML = "Nothing detected";
                }
            }
        )
}

