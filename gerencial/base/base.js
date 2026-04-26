
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("table").forEach(function (table) {
        const headers = [];

        table.querySelectorAll("thead th").forEach(function (th) {
            headers.push(th.innerText.trim());
        });

        table.querySelectorAll("tbody tr").forEach(function (row) {
            row.querySelectorAll("td").forEach(function (td, index) {
                if (headers[index]) {
                    td.setAttribute("data-label", headers[index]);
                }
            });
        });
    });
});
