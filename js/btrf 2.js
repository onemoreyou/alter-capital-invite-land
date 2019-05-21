let OFFER = 5;
let FLOW = 125;

let ipapiURL = 'https://ipapi.co/json/?key=861531b8c1d2314d1872eda57d819fb90ad8fc7c';

document.querySelector('#form-1').addEventListener("submit", function (e) {
    e.preventDefault();
    httpGetAsync(ipapiURL, (resp) => {
        pushLead(document.querySelector('#name-1').value,
            document.querySelector('#email-1').value,
            document.querySelector('#phone-1').value,
            JSON.parse(resp)['ip']);
    });
});

document.querySelector('#form-2').addEventListener("submit", function (e) {
    e.preventDefault();
    httpGetAsync(ipapiURL, (resp) => {
        pushLead('',
            '',
            document.querySelector('#phone-2').value,
            JSON.parse(resp)['ip']);
    });
});

document.querySelector('#form-3').addEventListener("submit", function (e) {
    e.preventDefault();
    httpGetAsync(ipapiURL, (resp) => {
        pushLead(document.querySelector('#name-3').value,
            document.querySelector('#email-3').value,
            document.querySelector('#phone-3').value,
            JSON.parse(resp)['ip']);
    });
});

document.querySelector('#form-4').addEventListener("submit", function (e) {
    e.preventDefault();
    httpGetAsync(ipapiURL, (resp) => {
        pushLead(document.querySelector('#name-4').value,
            document.querySelector('#email-4').value,
            document.querySelector('#phone-4').value,
            JSON.parse(resp)['ip']);
    });
});

function pushLead(name, email, phone, ip) {

    let data = {
        flow: FLOW,
        offer: OFFER,
        ip: ip,
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

function httpGetAsync(theUrl, callback) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
            callback(xmlHttp.responseText);
    }
    xmlHttp.open("GET", theUrl, true); // true for asynchronous
    xmlHttp.send(null);
}