((wp) => {
    if (quickdocs.admin_notice) {
        wp.data.dispatch('core/notices').createNotice(
            'info',
            quickdocs.admin_notice,
            {
                isDismissible: true,
            }
        );
    }
})(window.wp)