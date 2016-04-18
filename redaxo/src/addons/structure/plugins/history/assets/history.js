function rex_history_setLayer(data) {

    if ($("#content-history-layer").length) {
        $("#content-history-layer").replaceWith(data);

    } else {
        $("body").append(data);

    }

    $(".content-history-select").each(function (index) {

        var history_date = $(this).val();
        var iframe_id = $(this).attr("data-iframe");
        var iframe = $("#" + iframe_id);
        iframe.attr("src", history_article_link);

    });


    $("#content-history-layer").show();

    $(".content-history-select").on("change", function () {
        var history_date = $(this).val();
        var iframe_id = $(this).attr("data-iframe");
        var iframe = $("#" + iframe_id);
        if (history_date == "") {
            iframe.attr("src", history_article_link);

        } else {
            iframe.attr("src", history_article_link + "&rex_history_date=" + history_date);

        }

    });

}


function rex_history_snapVersion(select_id) {

    var history_date = $("#" + select_id).val();

    if (history_date == "") {
        return false;
    }

    $.ajax({
        url: "index.php?rex_history_function=snap&history_article_id=" + history_article_id + "&history_clang_id=" + history_clang_id + "&history_revision=" + history_revision + "&history_date=" + history_date,
        context: document.body

    }).done(function (data) {

        rex_history_setLayer(data);

        url = "index.php?page=content/edit&article_id=" + history_article_id + "&clang_id=" + history_clang_id + "&ctype=" + history_ctype_id + "&rex_set_version=" + history_revision;
        $.pjax({url: url, container: '#rex-js-page-main-content', fragment: '#rex-js-page-main-content'})

    });

};

function rex_history_openLayer() {

    $.ajax({
        url: "index.php?rex_history_function=layer&history_article_id=" + history_article_id + "&history_clang_id=" + history_clang_id + "&history_revision=" + history_revision,
        context: document.body

    }).done(function (data) {

        rex_history_setLayer(data);

    });

}


$(document).on("rex:ready", function (event, container) {
    container.on("click", '[data-history-layer="close"]', function (e) {
        e.preventDefault();
        $("#content-history-layer").hide();
    });
});
