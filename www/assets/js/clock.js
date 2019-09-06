function init_clock(idSelector) {
    var el = document.getElementById(idSelector);
    if (!el) {
        return;
    }
    var server_timestamp = el.getAttribute('data-time');
    var server_timestamp2 = 1000 * server_timestamp;
    var time_offset = server_timestamp2 - (new Date()).getTime();

    function updater() {
        var local_time = new Date();
        local_time.setTime(local_time.getTime() + time_offset);
        var min = local_time.getMinutes();
        var sec = local_time.getSeconds();
        document.getElementById('time').innerHTML = local_time.toLocaleString('cs-CZ');
    }
    setTimeout(function () {
        setInterval(updater, 1000);
        updater();
        }, 1000 - server_timestamp2 % 1000
    );
}
