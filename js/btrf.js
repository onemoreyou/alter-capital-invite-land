let OFFER = 7;
let FLOW = 121;

var form1 = document.querySelector('#form-1');

form1.addEventListener("submit", function (e) {
    e.preventDefault();
    pushLead(document.querySelector('#name-1').value,
        document.querySelector('#email-1').value,
        document.querySelector('#phone-1').value);
});

var form2 = document.querySelector('#form-2');

form2.addEventListener("submit", function (e) {
    e.preventDefault();
    pushLead(document.querySelector('#name-2').value,
        document.querySelector('#email-2').value,
        document.querySelector('#phone-2').value);
});

function pushLead(name, email, phone) {

    let data = {
        flow: FLOW,
        offer: OFFER,
        ip: window['userIP'],
        name: name,
        email: email,
        phone: phone
    }

    var xhr = new XMLHttpRequest();
    var url = "btrf.php";
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let getUrl = window.location;
            let baseUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
            window.location.href = `${baseUrl}/success.html`;
        }
    };

    xhr.send(JSON.stringify(data));
}