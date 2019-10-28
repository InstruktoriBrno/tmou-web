$('.file-manager').click(function () {
    var filemanagerWindow = window.open("/files", 'file_manager_' + Math.random(), "height=600,width=800");
    filemanagerWindow.fileManagerTargetId = this.getAttribute('data-target');
});
