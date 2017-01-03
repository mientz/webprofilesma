$(document).ready(function (e) {

    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
    $("img.lazy").lazyload();
});

/**
 * initialize the summernote editor
 * @param   {RegExp} elm element selector
 * @returns {function} init the summernote
 */
function globalSummernote(elm, textElm){

    var postmediaSelect = function (context) {
        var ui = $.summernote.ui

        // create button
        var button = ui.button({
            contents: '<i class="note-icon-picture"></i>',
            tooltip: 'hello',
            click: function(){
                globalPostmediaSelector("Masukkan Media Ke Dalam Pos", function(is_picked, image){
                    if(is_picked){
//                        alert(image)
                        elm.summernote('insertImage', image)
                    }
                })
            }
        });

        return button.render() // return button as jquery object
    }

    elm.summernote({
        height: null, // set editor height
        minHeight: 300, // set minimum height of editor
        maxHeight: null, // set maximum height of editor
        lang: 'id-ID',
        toolbar: [
            // [groupName, [list of button]]
            ['misc', ['codeview', 'undo', 'redo']],
            ['style', ['style', 'bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['text', ['ul', 'ol', 'paragraph', 'fontsize', 'color', 'height']],
            ['insert', ['postmediaSelect', 'link', 'video', 'hr']]
        ],
        buttons: {
            postmediaSelect: postmediaSelect
        },
        callbacks: {
            onChange: function (contents, $editable) {
                $(textElm).val(contents);
            }
        }
    });
}

/**
 * Pop the postmedia selector dialog
 * @param {string} title    set specified title
 * @param {function} callback return the picked value
 */
function globalPostmediaSelector(title, callback){
    var dialog = bootbox.dialog({
        title: title,
        message: Mustache.render($('#global-postmedia-dialog').html()),
        onEscape: true,
        backdrop: true,
        size: 'large',
        buttons: {
            select: {
                label: 'Gunakan Media',
                className: "btn-info",
                callback: function() {
                    callback(true, dialog.find('.global-postmedia-image-list option:selected').val());
                }
            },
            cancel: {
                label: 'Batal',
                className: "btn-default",
                callback: function() {

                }
            }
        }
    });
    dialog.init(function(){
        dialog.find('.global-postmedia-image-list-container').css('max-height', ($(window).height()-dialog.find('.modal-header').height()-dialog.find('.modal-footer').height()-400)+'px');
        dialog.find('.global-postmedia-image-list-container').css('height', ($(window).height()-dialog.find('.modal-header').height()-dialog.find('.modal-footer').height()-400)+'px');
        dialog.find('#postmedia-image-detail-sidebar').css('height', ($(window).height()-dialog.find('.modal-header').height()-dialog.find('.modal-footer').height()-360)+'px');
        dialog.find('.modal-header').css('background-color', '#2b3e50')
        dialog.find('.modal-header').html(Mustache.render($('#global-postmedia-dialog-header').html(), { title: title}));
        var myDropzone = new Dropzone('.global-postmedia-image-upload', {
            url: globalUrl.postAdminPostmediaUploadJSON,
            maxFileSize: 2,
            createImageThumbnails: true,
            aceptedFiles: 'image/*,',
            init: function () {
                this.on('complete', function (file) {
                    dialog.find('a[href="#global-postmedia-dialog-list-tab"]').tab('show')
                    dialog.find('.dz-preview').html('');
                })
            }
        });
        Dropzone.autoDiscover = false;
        Dropzone.options.myDropzone = false;

        pospulateImageOnTab('all');
        dialog.find('a[href="#global-postmedia-dialog-list-tab"]').tab('show');
        dialog.find('a[href="#global-postmedia-dialog-list-tab"]').on('show.bs.tab', function (e) {
            pospulateImageOnTab('all');
        })

        function pospulateImageOnTab(date){
            globalPopulatePostmediaList(date, function(data){
                dialog.find('.global-postmedia-image-list').html("<option value=''></option>");
                dialog.find('.global-postmedia-image-list').append(Mustache.render($('#global-postmedia-image-list').html(), data));
                dialog.find('.global-postmedia-image-list').imagepicker({
                    initialized: function(imagePicker){
                        dialog.find('.thumbnails.image_picker_selector li').addClass('col-md-2');
                        dialog.find('.thumbnails.image_picker_selector li').css('margin','0');
                        dialog.find('.thumbnails.image_picker_selector li').css('padding-left','0');
                    },
                    selected: function(select){
                        console.log(select.option);
                        globalPostmediaDetailExif(select.option.val(), function(data){
                            dialog.find('#postmedia-image-detail-sidebar').html(
                                Mustache.render($('#global-postmedia-dialog-image-detail').html(), data)
                            )
                        });
                    }
                });
            })
        }
    });
}

/**
 * Populate Post Media Image list
 * @param {string}   date     filter image list by date(Month Name and Year)
 * @param {function} callback Return the json data
 */
function globalPopulatePostmediaList(date, callback){
    $.getJSON(globalUrl.getAdminPostmediaListJSON, {date:date}, function(data){
        callback(data);
    });
}

/**
 * Populate postmedia select input filter
 * @param {function} callback return the option html
 */
function globalPopulatePostmediaFilterDateInput(callback){
    $.getJSON(globalUrl.getAdminPostmediaListJSON, {date:'all'}, function(data){
        var html = '<option value="all">Semua Tanggal</option>';
        $.each(data.time, function(key, val){
            html = html+'<option value="'+val+'">'+val+'</option>';
            callback(html);
        });
    });
}

/**
 * get postmedia image data
 * @param {number} id       postmedia id
 * @param {function} callback return exif data object
 */
function globalPostmediaDetailExif(id, callback){
    $.getJSON(globalUrl.getAdminPostmediaExifJSON + "/" + id, function(data){
        callback(data);
    });
}

/**
 * Shorthadn helper for post category
 * @param {string} value    if value is not set then get category list
 * @param {function} callback if value is set then add category api
 */
function globalShorthandCategory(value, callback){
    if(typeof value === "function" && typeof callback === "undefined"){
        $.getJSON(globalUrl.getAdminCategoryListJSON, function(data){
            value(data);
        });
    }else if(typeof callback === "function"){
        $.post(globalUrl.postAdminCategoryShorthandInsertJSON, {name: value}, function (data) {
            if(data.inserted){
                $.getJSON(globalUrl.getAdminCategoryListJSON, function(data){
                    callback(data);
                });
            }
        }, 'json')
    }
}

/**
 * save post data function (inculding autosave)
 * @param   {function} elm      form element
 * @param   {string} type     type data to post
 * @param   {number} id       id post, empty string if first load to crete post data into db
 * @param   {function} callback return the ajax post data
 * @param   {boolean} ret      tru to default browser submit(redirect post)
 * @returns {boolean} return the ret value
 */
function SavePostChange(elm, type, id, callback, ret){
    $(elm).ajaxSubmit({
        url: globalUrl.postAdminPostSaveJSON+(id == '' ? '' : '/'+id),
        data:{type:type},
        dataType: 'json',
        success:function(res, status, xhr, jq){
            $(elm).attr('action', globalUrl.postAdminPostSaveJSON+'/'+res.id)
            callback(res);
            if(ret){
                $(elm).unbind('submit').submit();
            }
        }
    });
    return ret
}

/**
 * save pages data function (inculding autosave)
 * @param   {function} elm      form element
 * @param   {string} type     type data to post
 * @param   {number} id       id post, empty string if first load to crete post data into db
 * @param   {function} callback return the ajax post data
 * @param   {boolean} ret      tru to default browser submit(redirect post)
 * @returns {boolean} return the ret value
 */
function SavePagesChange(elm, type, id, callback, ret){
    $(elm).ajaxSubmit({
        url: globalUrl.postAdminPagesSaveJSON+(id == '' ? '' : '/'+id),
        data:{type:type},
        dataType: 'json',
        success:function(res, status, xhr, jq){
            $(elm).attr('action', globalUrl.postAdminPagesSaveJSON+'/'+res.id)
            callback(res);
            if(ret){
                $(elm).unbind('submit').submit();
            }
        }
    });
    return ret
}

/*
 * ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 * ################################################################################################################################################
 * %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 */

/**
 * run When Postmedia Page is Load
 */
function postmediaPage(){
    bootbox.setLocale('id')
    var postmediaList = new List('media-list', {
        valueNames: ['date']
    });
    $('#postmedia-select-date').on('change', function(){
        if ($(this).val() != '*') {
            postmediaList.filter(function (item) {
                if (item.values().date == $('#postmedia-select-date').val() || item.values().date == '*') {
                    return true;
                } else {
                    return false;
                }
            });
        } else {
            postmediaList.filter();
        }
    });
    if($('.postmedia-photo-list').length){
        var uploadDropzone = new Dropzone('.postmedia-photo-list', {
            url: globalUrl.postAdminPostmediaUploadJSON,
            acceptedFiles: 'image/*',
            previewTemplate: Mustache.render($('#postmedia-upload-preview-tmpl').html()),
            maxThumbnailFilesize: 100,
            clickable: '#postmedia-add-photo',
            uploadprogress: function(file, progress, bytesSent) {
                var elm = file.previewElement
                $(elm).find('.progress-bar').width(progress+'%')
                var img = $(elm).find('img');
                if(progress == 100){
                    $(elm).find('.progress').css('display', 'none');
                    $(elm).find('img').css('filter', '');
                }else{
                    $(elm).find('img').css('filter', 'blur(5px)');
                }
            },
            success: function(file, res){
                var elm = file.previewElement
                if(res.success){
                    $(elm).find('.postmedia-image-thumb-container').attr('href', 'public/gallery/'+res.file);
                    $(elm).find('.postmedia-photo-rename').data('id', res.id);
                    $(elm).find('.postmedia-photo-move').data('id', res.id);
                    $(elm).find('.postmedia-photo-delete').data('id', res.id);
                }
                if(res.failed){
                    $(elm).find('.progress-container').html('<a class="alert alert-dismissible alert-danger postmedia-add-photo-error" style="margin-left: auto;margin-right: auto;width:80%;">Gagal</a>')
                }
            },
            complete: function(){
                location.reload();
            }
        });
    }

    $('.postmedia-photo-delete').on('click', function(){
        var name = $(this).data('title');
        var id = $(this).data('id');
        var file = $(this).data('file');
        bootbox.confirm({
            className: 'modal-danger',
            title: 'Hapus Media ?',
            message: "Apakah anda yakin ingin menghapus <span class='text-info'>"+name+"</span>?",
            buttons: {
                confirm: {
                    label: 'Ya',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'Batal',
                    className: 'btn-default'
                }
            },
            callback: function(result){
                $.post(globalUrl.getAdminPostmediaDeleteJSON+"/"+id+'/'+file, function(data){
                    if(data.success){
                        location.reload();
                    }
                })
            }
        });
    });
}

/**
 * edit post page specific functions
 * @param {object} post_data post data from db for edit
 */
function editPostPage(post_data){
    // cropit init
    var imageCropper = $('#edit-post-header-image-cropper')
    imageCropper.cropit({
        width: 564,
        height: 215,
        allowDragNDrop:false,
        smallImage: 'allow',
        minZoom: 'fill',
        onImageError: function(error){
            alert(error);
        },
        onImageLoaded: function(){
                $('#edit-post-header-value').val(imageCropper.cropit('export', {
                    type: 'image/jpeg',
                    quality: 1,
                }));
        },
        onOffsetChange: function(offset){
            var data = {
                image: imageCropper.data('image'),
                size: imageCropper.cropit('previewSize'),
                offset: offset,
                zoom: imageCropper.cropit('zoom'),
            }
            localStorage.imageCroped = JSON.stringify(data);
            $('#edit-post-header-value').val(imageCropper.cropit('export', {
                type: 'image/jpeg',
                quality: 1,
            }));
        },
        onZoomChange: function(zoom){
            if(typeof imageCropper.cropit('offset') != 'undefined'){
                localStorage.imageCroped = JSON.stringify(data);
                $('#edit-post-header-value').val(imageCropper.cropit('export', {
                    type: 'image/jpeg',
                    quality: 1,
                }));
            }
        },
    });

    //tag input init and populate tags typeahead source
    $('#edit-post-tags-input').tagsinput({
        typeahead:{
            source: function(query) {
                return $.get(globalUrl.getAdminTagsListJSON, function(data){
                    return data;
                }, 'json');
            },
            afterSelect: function() {
                $('#edit-post-tags-input').tagsinput('input').val('');
            }
        },
        freeInput: true
    });

    imageCropper.cropit('imageSrc', imageCropper.data('rawimage'));
    imageCropper.data('image', imageCropper.data('rawimage'));

    // populate category data
    globalShorthandCategory(function(data){
        $('#edit-post-category-list').empty();
        $.each(data, function (key, val) {
            if (val.id == $('#edit-post-category-list').data('category')) {
                val.checked = 'checked'
            }
            $('#edit-post-category-list').append(Mustache.render($('#edit-post-category-list-tmpl').html(), val));
        });
    });

    // actions on image header add
    $('#edit-post-header-select-add').on('click', function(){
        globalPostmediaSelector("Pilih Gambar Fitur", function(is_picked, image){
            if(is_picked){
                $('#edit-post-header').removeClass('hidden');
                $('#edit-post-header-select-add').addClass('hidden')
                imageCropper.cropit('imageSrc', image);
                imageCropper.data('image', image);
            }else{
                $('#edit-post-header').addClass('hidden');
            }
        })
    })

    // actions on image header change
    $('#edit-post-header-select-change').on('click', function(){
        globalPostmediaSelector("Ganti Gambar Fitur", function(is_picked, image){
            if(is_picked){
                imageCropper.cropit('imageSrc', image);
                imageCropper.data('image', image);
            }
        })
    })

    // actions on image header remove
    $('#edit-post-header-select-remove').on('click', function(){
        $('#edit-post-header-select-add').removeClass('hidden')
        $('#edit-post-header').addClass('hidden');
        $('#edit-post-header-value').val('');
    })

    // actions on category add
    $('#edit-post-category-add-trigger').on('click', function(){
        var input = $('#edit-post-category-add-input').val()
        if(input != ''){
            globalShorthandCategory(input, function(data){
                $('#edit-post-category-list').empty();
                $.each(data, function (key, val) {
                    if (val.id == $('#edit-post-category-list').data('category')) {
                        val.checked = 'checked'
                    }
                    $('#edit-post-category-list').append(Mustache.render($('#edit-post-category-list-tmpl').html(), val));
                });
                $('.add-kategori').collapse('hide');
                $('#edit-post-category-add-input').val('');
            })
        }
    })
    //actions

    $('#edit-post-recover-autosave').on('click', function(){
        var recover_data = $(this).data('recover')
        console.log(recover_data)
        // populate image header data
        $.getJSON(globalUrl.getAdminPostmediaCropitJson + '/' + recover_data.header_image, function(data){
            if(data.image != 'no-image'){
                $('#edit-post-header').removeClass('hidden');
                $('#edit-post-header-select-add').addClass('hidden')
                imageCropper.cropit('imageSrc', 'public/content/'+data.image);
                imageCropper.data('image', data.image);
            }else{
                $('#edit-post-header-select-add').removeClass('hidden')
                $('#edit-post-header').addClass('hidden');
                $('#edit-post-header-value').val('');
            }
        });

        // populate category data
        globalShorthandCategory(function(data){
            $('#edit-post-category-list').empty();
            $.each(data, function (key, val) {
                if (val.id == recover_data.category_id) {
                    val.checked = 'checked'
                }
                $('#edit-post-category-list').append(Mustache.render($('#edit-post-category-list-tmpl').html(), val));
            });
        });

        $('#edit-post-tags-input').tagsinput('removeAll');
        $('#edit-post-tags-input').tagsinput('add', recover_data.tags);

        $('input[name=title]').val(recover_data.title)
        $('#edit-post-content-editor').html(recover_data.content);
        $('#edit-post-content-editor').summernote('reset');
        $('#edit-post-content-value').val(recover_data.content)
        $(this).parent().remove();
    })

    $('#edit-post-form').submit(function(e){
        e.preventDefault();
    });

    $('.edit-post-publish-trigger').on('click', function(){
        SavePostChange($('#edit-post-form'), 'publish-saved', $('#edit-post-form').data('id'), function(res){
            window.location.replace(globalUrl.getAdminPostListHTML);
        });
    });

    $('.edit-post-draft-trigger').on('click', function(){
        alert('lala');
        SavePostChange($('#edit-post-form'), 'draft-saved', $('#edit-post-form').data('id'), function(res){
            window.location.replace(globalUrl.getAdminPostListHTML);
        });
    });

    setInterval(function(){
        $('.page-title').find('i').removeClass('hidden');
        if( typeof $('#edit-post-form').data('id') !== 'undefined'){
            SavePostChange($('#edit-post-form'), $('#edit-post-form').data('status')+'-autosave', $('#edit-post-form').data('id'), function(res){
                console.log({
                    res:res,
                    type: $('#edit-post-form').data('status')+'-autosave'
                })
                $('.page-title').find('i').addClass('hidden');
            });
        }
    }, 60000);
}

/**
 * [[Description]]
 * @returns {[[Type]]} [[Description]]
 */
function addPostPage(){
    localStorage.setItem('imageCroped', '');
    localStorage.setItem('post_id', '');
    // cropit init
    var imageCropper = $('#add-post-header-image-cropper')
    imageCropper.cropit({
        width: 564,
        height: 215,
        allowDragNDrop:false,
        smallImage: 'allow',
        onImageError: function(error){
            alert(error);
        },
        onImageLoaded: function(){
            var data = {
                image: imageCropper.data('image'),
                size: imageCropper.cropit('previewSize'),
                offset: imageCropper.cropit('offset'),
                zoom: imageCropper.cropit('zoom'),
            }
            localStorage.imageCroped = JSON.stringify(data);
            $('#add-post-header-value').val(imageCropper.cropit('export', {
                type: 'image/jpeg',
                quality: 1,
            }));
        },
        onOffsetChange: function(offset){
            var data = {
                image: imageCropper.data('image'),
                size: imageCropper.cropit('previewSize'),
                offset: offset,
                zoom: imageCropper.cropit('zoom'),
            }
            localStorage.imageCroped = JSON.stringify(data);
            $('#add-post-header-value').val(imageCropper.cropit('export', {
                type: 'image/jpeg',
                quality: 1,
            }));
        },
        onZoomChange: function(zoom){
            if(typeof imageCropper.cropit('offset') != 'undefined'){
                var data = {
                    image: imageCropper.data('image'),
                    size: imageCropper.cropit('previewSize'),
                    offset: imageCropper.cropit('offset'),
                    zoom: zoom,
                }
                localStorage.imageCroped = JSON.stringify(data);
                $('#add-post-header-value').val(imageCropper.cropit('export', {
                    type: 'image/jpeg',
                    quality: 1,
                }));
            }
        },
    });

    //tag input init and populate tags typeahead source
    $('#add-post-tags-input').tagsinput({
        typeahead:{
            source: function(query) {
                return $.get(globalUrl.getAdminTagsListJSON, function(data){
                    return data;
                }, 'json');
            },
            afterSelect: function() {
                $('#add-post-tags-input').tagsinput('input').val('');
            }
        },
        freeInput: true
    });

    // populate category data
    globalShorthandCategory(function(data){
        $('#add-post-category-list').empty();
        $.each(data, function (key, val) {
            if (key == 0) {
                val.checked = 'checked'
            }
            $('#add-post-category-list').append(Mustache.render($('#add-post-category-list-tmpl').html(), val));
        });
    });

    // actions on image header add
    $('#add-post-header-select-add').on('click', function(){
        localStorage.setItem('imageCroped', '');
        globalPostmediaSelector("Pilih Gambar Fitur", function(is_picked, image){
            if(is_picked){
                $('#add-post-header').removeClass('hidden');
                $('#add-post-header-select-add').addClass('hidden')
                imageCropper.cropit('imageSrc', image);
                imageCropper.data('image', image);
            }else{
                $('#add-post-header').addClass('hidden');
            }
        })
    })

    // actions on image header change
    $('#add-post-header-select-change').on('click', function(){
        localStorage.setItem('imageCroped', '');
        globalPostmediaSelector("Ganti Gambar Fitur", function(is_picked, image){
            if(is_picked){
                imageCropper.cropit('imageSrc', image);
                imageCropper.data('image', image)
            }
        })
    })

    // actions on image header remove
    $('#add-post-header-select-remove').on('click', function(){
        localStorage.setItem('imageCroped', '');
        $('#add-post-header-select-add').removeClass('hidden')
        $('#add-post-header').addClass('hidden');
        $('#add-post-header-value').val('');
        imageCropper.data('image', '')
    })

    // actions on category add
    $('#add-post-category-add-trigger').on('click', function(){
        var input = $('#add-post-category-add-input').val()
        if(input != ''){
            globalShorthandCategory(input, function(data){
                $('#add-post-category-list').empty();
                $.each(data, function (key, val) {
                    if (val.id == $('#add-post-category-list').data('category')) {
                        val.checked = 'checked'
                    }
                    $('#add-post-category-list').append(Mustache.render($('#add-post-category-list-tmpl').html(), val));
                });
                $('.add-kategori').collapse('hide');
                $('#add-post-category-add-input').val('');
            })
        }
    })

    $('input[name=title]').on('focusout', function(){
        if($(this).val() != '' && typeof $('#add-post-form').data('id') === 'undefined'){
            SavePostChange('#add-post-form', 'new', '', function(res){
                $('#add-post-form').data('id', res.id);
                console.log({
                    res:res,
                    type: 'draft-created'
                })
                $('#add-post-save-type').val('draft-autosave');
            });
        }
    });

    $('#add-post-form').submit(function(e){
        e.preventDefault();
    });

    $('#add-post-publish-trigger').on('click', function(){
        SavePostChange($('#add-post-form'), 'publish-saved', $('#add-post-form').data('id'), function(res){
            window.location.replace(globalUrl.getAdminPostEditHTML+'/'+$('#add-post-form').data('id'));
        });
    });

    $('#add-post-draft-trigger').on('click', function(){
        SavePostChange($('#add-post-form'), 'draft-saved', $('#add-post-form').data('id'), function(res){
            window.location.replace(globalUrl.getAdminPostEditHTML+'/'+$('#add-post-form').data('id'));
        });
    });

    setInterval(function(){
        if( typeof $('#add-post-form').data('id') !== 'undefined'){
            SavePostChange($('#add-post-form'), 'draft-autosave', $('#add-post-form').data('id'), function(res){
                console.log({
                    res:res,
                    type: 'draft-autosave'
                })
            });
            console.log('autosave');
        }
    }, 60000);
}


function postListPage(){
    // list.js table declarations
    var postlist = new List('post-list', {
        valueNames: ['title', 'category', 'date'],
        page: 5,
        plugins: [ListPagination({
            outerWindow: 1,
            innerWindow: 2
        })]
    });

    // list table filter by category
    $('#post-list-filter-category-trigger').on('change', function () {
        if ($(this).val() != '*') {
            postlist.filter(function (item) {
                if (item.values().category == $('#post-list-filter-category-trigger').val()) {
                    return true;
                } else {
                    return false;
                }
            });
        } else {
            postlist.filter();
        }
    })

    // list table filter by category
    $('#post-list-filter-date-trigger').on('change', function () {
        if ($(this).val() != '*') {
            postlist.filter(function (item) {
                if (item.values().date == $('#post-list-filter-date-trigger').val()) {
                    return true;
                } else {
                    return false;
                }
            });
        } else {
            postlist.filter();
        }
    });

    $('.post-trash-sub').on('click', function () {
        var id = $(this).data('id');
        var dialog = bootbox.dialog({
            message: '<p class="text-center"><i class="fa fa-spin fa-spinner"></i> Membuang Ke Tong sampah</p>',
            closeButton: false
        });
        postChangeAction('trash', $(this).data('id'));
    });

    $('.post-revert-sub').on('click', function () {
        var dialog = bootbox.dialog({
            message: '<p class="text-center"><i class="fa fa-spin fa-spinner"></i> Mengembalikan Pos</p>',
            closeButton: false
        });
        postChangeAction('revert', $(this).data('id'));
    })

    $('.post-delete-sub').on('click', function () {
        var post_button = $(this).parent().parent().parent().clone();
        post_button.find('.checkbox').parent().remove();
        post_button.find('.form-inline').remove();
        var post_button = post_button.html();
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Apakah Anda Yakin Akan Menghapus Permanent Pos ini?",
            message: '<table class="table table-striped post-list"><thead><tr class="info"><th>Judul</th><th style="width:10em;">Penulis</th><th style="width:10em;">Kategori</th><th style="width:10em;">Tag</th><th style="width:10em;">Tanggal</th></tr></thead><tbody class="list">' + post_button + "</tbody></table>",
            size: 'large',
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Batal'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Hapus Permanen',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result) {
                    postChangeAction('delete', id);
                }
            }
        });
    });

    $('#post-list-batch-action').on('change', function(){
        var val = $(this).val();
        switch(val){
            case '*':
                $('#post-list-batch-action-trigger').addClass('disabled');
                break;
            default:
                $('#post-list-batch-action-trigger').removeClass('disabled');
                break;
        }
    });

    $('#post-list-batch-action-trigger').on('click', function () {
        var action = $('#post-list-batch-action').val();
        var len = $('.post-checkbox:checked').length;
        var data = [];
        $('.post-checkbox:checked').each(function(key, val){
            data.push({
                id: $(this).val(),
                element: $(this).parent().parent().parent().clone()
            })

            if(key == len - 1){
                switch(action){
                    case 'delete':
                        var list_delete = '';
                        $('.post-checkbox:checked').each(function (num, elm) {
                            var current_html = $(this).parent().parent().parent().clone();
                            current_html.find('.form-inline').remove();
                            current_html.find('.checkbox').parent().remove();
                            list_delete = list_delete+'<tr>'+current_html.html()+'</tr>';
                        })
                        bootbox.confirm({
                            title: "Apakah Anda Yakin Akan Menghapus Permanent Pos ini?",
                            message: '<table class="table table-striped post-list"><thead><tr class="info"><th>Judul</th><th style="width:10em;">Penulis</th><th style="width:10em;">Kategori</th><th style="width:10em;">Tag</th><th style="width:10em;">Tanggal</th></tr></thead><tbody class="list">' + list_delete + "</tbody></table>",
                            size: 'large',
                            buttons: {
                                cancel: {
                                    label: '<i class="fa fa-times"></i> Batal'
                                },
                                confirm: {
                                    label: '<i class="fa fa-check"></i> Hapus Permanen',
                                    className: 'btn-danger'
                                }
                            },
                            callback: function (result) {
                                if (result) {
                                    postChangeAction(action, data)
                                }
                            }
                        });
                        break;
                    case '*':

                        break;
                    default:
                        postChangeAction(action, data)
                        break;
                }
            }
        });
    })

    function postChangeAction(action, id) {
        if(typeof id !== 'object'){
            $.post(globalUrl.postAdminPostChangeJSON + "/" + id + "/" + action, function (data) {
                if (data.success) {
                    location.reload();
                }
            }, "json");
        }else{
            var dialog = bootbox.dialog({
                message: '<table class="table table-striped post-list"><thead><tr class="info"><th style="width:1em;"></th><th>Judul</th><th style="width:10em;">Penulis</th><th style="width:10em;">Kategori</th><th style="width:10em;">Tag</th><th style="width:10em;">Tanggal</th></tr></thead><tbody class="list"></tbody></table>',
                closeButton: false,
                size: 'large',
            });
            $.each(id, function(key, val){
                var elm = val.element
                elm.find('#id-container'+val.id).html('<i class="fa fa-spinner fa-spin"></i>')
                elm.find('.form-inline').remove();
                dialog.find('.list').append(elm);
                setTimeout(function(){
                    $.post(globalUrl.postAdminPostChangeJSON + "/" + val.id + "/" + action, function (data) {
                        if (data.success) {
                            dialog.find('#id-container'+val.id).html('<i class="fa fa-check-circle-o text-success"></i>');
                            if(key == id.length - 1){
                                location.reload();
                            }
                        }else{
                            dialog.find('#id-container'+val.id).html('<i class="fa fa-exclamation-circle text-danger"></i>');
                        }
                    }, "json");
                }, 1000)
            });
        }

    }
}

function pagesListPage(){
    // list.js table declarations
    var pagelist = new List('pages-list', {
        valueNames: ['title', 'date'],
        page: 5,
        plugins: [ListPagination({
            outerWindow: 1,
            innerWindow: 2
        })]
    });

    // list table filter by category
    $('#pages-list-filter-date-trigger').on('change', function () {
        if ($(this).val() != '*') {
            pagelist.filter(function (item) {
                if (item.values().date == $('#pages-list-filter-date-trigger').val()) {
                    return true;
                } else {
                    return false;
                }
            });
        } else {
            pagelist.filter();
        }
    });

    $('.pages-trash-sub').on('click', function () {
        var id = $(this).data('id');
        var dialog = bootbox.dialog({
            message: '<p class="text-center"><i class="fa fa-spin fa-spinner"></i> Membuang Ke Tong sampah</p>',
            closeButton: false
        });
        pagesChangeAction('trash', $(this).data('id'));
    });

    $('.pages-revert-sub').on('click', function () {
        var dialog = bootbox.dialog({
            message: '<p class="text-center"><i class="fa fa-spin fa-spinner"></i> Mengembalikan Pos</p>',
            closeButton: false
        });
        pagesChangeAction('revert', $(this).data('id'));
    })

    $('.pages-delete-sub').on('click', function () {
        var pages_button = $(this).parent().parent().parent().clone();
        pages_button.find('.checkbox').parent().remove();
        pages_button.find('.form-inline').remove();
        var pages_button = pages_button.html();
        var id = $(this).data('id');
        bootbox.confirm({
            title: "Apakah Anda Yakin Akan Menghapus Permanent Pos ini?",
            message: '<table class="table table-striped post-list"><thead><tr class="info"><th>Judul</th><th style="width:10em;">Penulis</th><th style="width:10em;">Kategori</th><th style="width:10em;">Tag</th><th style="width:10em;">Tanggal</th></tr></thead><tbody class="list">' + pages_button + "</tbody></table>",
            size: 'large',
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Batal'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Hapus Permanen',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result) {
                    pagesChangeAction('delete', id);
                }
            }
        });
    });

    $('#pages-list-batch-action').on('change', function(){
        var val = $(this).val();
        switch(val){
            case '*':
                $('#pages-list-batch-action-trigger').addClass('disabled');
                break;
            default:
                $('#pages-list-batch-action-trigger').removeClass('disabled');
                break;
        }
    });

    $('#pages-list-batch-action-trigger').on('click', function () {
        var action = $('#pages-list-batch-action').val();
        var len = $('.pages-checkbox:checked').length;
        var data = [];
        $('.pages-checkbox:checked').each(function(key, val){
            data.push({
                id: $(this).val(),
                element: $(this).parent().parent().parent().clone()
            })

            if(key == len - 1){
                switch(action){
                    case 'delete':
                        var list_delete = '';
                        $('.pages-checkbox:checked').each(function (num, elm) {
                            var current_html = $(this).parent().parent().parent().clone();
                            current_html.find('.form-inline').remove();
                            current_html.find('.checkbox').parent().remove();
                            list_delete = list_delete+'<tr>'+current_html.html()+'</tr>';
                        })
                        bootbox.confirm({
                            title: "Apakah Anda Yakin Akan Menghapus Permanent Halaman ini?",
                            message: '<table class="table table-striped post-list"><thead><tr class="info"><th>Judul</th><th style="width:10em;">Penulis</th><th style="width:10em;">Kategori</th><th style="width:10em;">Tag</th><th style="width:10em;">Tanggal</th></tr></thead><tbody class="list">' + list_delete + "</tbody></table>",
                            size: 'large',
                            buttons: {
                                cancel: {
                                    label: '<i class="fa fa-times"></i> Batal'
                                },
                                confirm: {
                                    label: '<i class="fa fa-check"></i> Hapus Permanen',
                                    className: 'btn-danger'
                                }
                            },
                            callback: function (result) {
                                if (result) {
                                    pagesChangeAction(action, data)
                                }
                            }
                        });
                        break;
                    case '*':

                        break;
                    default:
                        pagesChangeAction(action, data)
                        break;
                }
            }
        });
    })

    function pagesChangeAction(action, id) {
        if(typeof id !== 'object'){
            $.post(globalUrl.postAdminPagesChangeJSON + "/" + id + "/" + action, function (data) {
                if (data.success) {
                    location.reload();
                }
            }, "json");
        }else{
            var dialog = bootbox.dialog({
                message: '<table class="table table-striped post-list"><thead><tr class="info"><th style="width:1em;"></th><th>Judul</th><th style="width:10em;">Penulis</th><th style="width:10em;">Kategori</th><th style="width:10em;">Tag</th><th style="width:10em;">Tanggal</th></tr></thead><tbody class="list"></tbody></table>',
                closeButton: false,
                size: 'large',
            });
            $.each(id, function(key, val){
                var elm = val.element
                elm.find('#id-container'+val.id).html('<i class="fa fa-spinner fa-spin"></i>')
                elm.find('.form-inline').remove();
                dialog.find('.list').append(elm);
                setTimeout(function(){
                    $.post(globalUrl.postAdminPagesChangeJSON + "/" + val.id + "/" + action, function (data) {
                        if (data.success) {
                            dialog.find('#id-container'+val.id).html('<i class="fa fa-check-circle-o text-success"></i>');
                            if(key == id.length - 1){
                                location.reload();
                            }
                        }else{
                            dialog.find('#id-container'+val.id).html('<i class="fa fa-exclamation-circle text-danger"></i>');
                        }
                    }, "json");
                }, 1000)
            });
        }

    }
}



function addPagesPage(){
    $('input[name=title]').on('focusout', function(){
        if($(this).val() != '' && typeof $('#add-post-form').data('id') === 'undefined'){
            SavePagesChange('#add-pages-form', 'new', '', function(res){
                $('#add-pages-form').data('id', res.id);
                console.log({
                    res:res,
                    type: 'draft-created'
                })
                $('#add-pages-save-type').val('draft-autosave');
            });
        }
    });

    $('#add-pages-form').submit(function(e){
        e.preventDefault();
    });

    $('#add-pages-publish-trigger').on('click', function(){
        SavePagesChange($('#add-pages-form'), 'saved', $('#add-pages-form').data('id'), function(res){
            window.location.replace(globalUrl.getAdminPagesEditHTML+'/'+$('#add-pages-form').data('id'));
        });
    });

    setInterval(function(){
        if( typeof $('#add-pages-form').data('id') !== 'undefined'){
            SavePagesChange($('#add-pages-form'), 'autosave', $('#add-pages-form').data('id'), function(res){
                console.log({
                    res:res,
                    type: 'autosave'
                })
            });
            console.log('autosave');
        }
    }, 60000);
}


function editPagesPage(){

    $('#edit-pages-recover-autosave').on('click', function(){
        var recover_data = $(this).data('recover')
        console.log(recover_data)

        $('#edit-pages-tags-input').tagsinput('removeAll');
        $('#edit-pages-tags-input').tagsinput('add', recover_data.tags);

        $('input[name=title]').val(recover_data.title)
        $('#edit-pages-content-editor').html(recover_data.content);
        $('#edit-pages-content-editor').summernote('reset');
        $('#edit-pages-content-value').val(recover_data.content)
        $(this).parent().remove();
    })

    $('#edit-pages-form').submit(function(e){
        e.preventDefault();
    });

    $('#edit-pages-publish-trigger').on('click', function(){
        SavePagesChange($('#edit-pages-form'), 'saved', $('#edit-pages-form').data('id'), function(res){
            window.location.replace(globalUrl.getAdminPagesListHTML);
        });
    });

    setInterval(function(){
        console.log('lala');
        $('.page-title').find('i').removeClass('hidden');
        if( typeof $('#edit-pages-form').data('id') !== 'undefined'){
            SavePagesChange($('#edit-pages-form'), 'autosave', $('#edit-pages-form').data('id'), function(res){
                console.log({
                    res:res,
                    type: 'autosave'
                })
                $('.page-title').find('i').addClass('hidden');
            });
        }
    }, 60000);
}

function customPageGallery(){
    bootbox.setLocale('id')
    $('.gallery-album-container').hover(function(){
        var elm = $(this)
        elm.find('.album-image-thumb-container').cycle({
            pause: false,
            pauseOnPagerHover: false,
            speed: 1000,
            timeout: 1000,
        });
    }, function(){
        var elm = $(this)
        elm.find('.album-image-thumb-container').cycle('destroy');
        elm.find('.album-image-thumb-container').find('img').css('opacity', '0');
        elm.find('.album-image-thumb-container').find('img').first().css('opacity', '1');
    })
    $('.gallery-album-container').each(function(index, elm){
        $(elm).find('img').each(function(i, img){
            $(img).css('opacity', '1');
            $(img).css('margin-left', '-'+($(img).width()-$(elm).width())/2+'px');
        })
    });

    $('#gallery-add-album').on('click', function(){
        bootbox.prompt("Buat Album Baru", function(result){
            $.post(globalUrl.postAdminCustomGalleryJSON+'/albums-add', {name:result}, function(data){
                if(data.success){
                    window.location.replace(globalUrl.getAdminCustomGalleryHTML+'/'+data.id);
                }
            }, 'json');
        });
    });
    if($('.gallery-photo-list').length){
        var uploadDropzone = new Dropzone('.gallery-photo-list', {
            url: globalUrl.postAdminCustomGalleryJSON+"/photo-add/"+$('.gallery-photo-list').data('id'),
            acceptedFiles: 'image/*',
            previewTemplate: Mustache.render($('#gallery-upload-preview-tmpl').html()),
            maxThumbnailFilesize: 100,
            clickable: '#gallery-add-photo',
            uploadprogress: function(file, progress, bytesSent) {
                var elm = file.previewElement
                $(elm).find('.progress-bar').width(progress+'%')
                var img = $(elm).find('img');
                if(progress == 100){
                    $(elm).find('.progress').css('display', 'none');
                    $(elm).find('img').css('filter', '');
                }else{
                    $(elm).find('img').css('filter', 'blur(5px)');
                }
            },
            success: function(file, res){
                var elm = file.previewElement
                if(res.success){
                    $(elm).find('.album-image-thumb-container').attr('href', 'public/gallery/'+res.file);
                    $(elm).find('.gallery-photo-rename').data('id', res.id);
                    $(elm).find('.gallery-photo-move').data('id', res.id);
                    $(elm).find('.gallery-photo-delete').data('id', res.id);
                }
                if(res.failed){
                    $(elm).find('.progress-container').html('<a class="alert alert-dismissible alert-danger gallery-add-photo-error" style="margin-left: auto;margin-right: auto;width:80%;">Gagal</a>')
                }
            },
            complete: function(){
                location.reload();
            }
        });
    }

    $('.gallery-album-change-public').on('click', function(){
        var elm = $(this)
        galleryChange($(this).data('id'), 'album-to-public', function(data){
            if(data.success){
                elm.parent().parent().prev().find('i').removeClass('fa-lock');
                elm.parent().parent().prev().find('i').addClass('fa-globe');
            }
        })
    })
    $('.gallery-album-change-hidden').on('click', function(){
        var elm = $(this)
        galleryChange($(this).data('id'), 'album-to-hidden', function(data){
            if(data.success){
                elm.parent().parent().prev().find('i').removeClass('fa-globe');
                elm.parent().parent().prev().find('i').addClass('fa-lock')
            }
        })
    });

    $('.gallery-album-remove').on('click', function(){
        var name = $(this).data('name');
        var id = $(this).data('id');
        bootbox.confirm({
            className: 'modal-danger',
            title: 'Hapus Album ?',
            message: "Apakah anad yakin ingin menghapus <span class='text-info'>"+name+"</span>? <span class='text-warning'>Semua foto yang tedapat pada album ini akan terhapus juga</span>.",
            buttons: {
                confirm: {
                    label: 'Ya',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'Batal',
                    className: 'btn-default'
                }
            },
            callback: function(result){
                if(result){
                    galleryChange(id, 'album-delete', function(data){
                        location.replace(globalUrl.getAdminCustomGalleryHTML);
                    })
                }
            }
        });
    });

    $('.gallery-album-rename').on('click', function(){
        var name = $(this).data('name');
        var id = $(this).data('id');
        bootbox.prompt({
            title: 'Ganti Nama Album',
            value: name,
            buttons: {
                confirm: {
                    label: 'Ya',
                    className: 'btn-info'
                },
                cancel: {
                    label: 'Batal',
                    className: 'btn-default'
                }
            },
            callback: function(result){
                if(result !== null){
                    $.post(globalUrl.postAdminCustomGalleryJSON+"/album-rename/"+id, {name:result}, function(data){
                        if(data.success){
                            location.reload();
                        }
                    })
                }
            }
        });
    });

    $('.gallery-photo-rename').on('click', function(){
        var name = $(this).data('title');
        var id = $(this).data('id');
        bootbox.prompt({
            title: 'Ganti Nama Foto',
            value: name,
            buttons: {
                confirm: {
                    label: 'Ya',
                    className: 'btn-info'
                },
                cancel: {
                    label: 'Batal',
                    className: 'btn-default'
                }
            },
            callback: function(result){
                if(result !== null){
                    $.post(globalUrl.postAdminCustomGalleryJSON+"/photo-rename/"+id, {name:result}, function(data){
                        if(data.success){
                            location.reload();
                        }
                    })
                }
            }
        });
    });

    $('.gallery-photo-move').on('click', function(){
        var name = $(this).data('title');
        var id = $(this).data('id');
        var albumId = $(this).data('albumid');
        var albums = $(this).data('albums');
        bootbox.prompt({
            title: 'Ganti Nama Foto',
            value: albumId,
            inputType: 'select',
            inputOptions: albums,
            buttons: {
                confirm: {
                    label: 'Ya',
                    className: 'btn-info'
                },
                cancel: {
                    label: 'Batal',
                    className: 'btn-default'
                }
            },
            callback: function(result){
                if(result !== null){
                    $.post(globalUrl.postAdminCustomGalleryJSON+"/photo-move/"+id, {albumid:result}, function(data){
                        if(data.success){
                            location.reload();
                        }
                    })
                }
            }
        });
    });

    $('.gallery-photo-delete').on('click', function(){
        var name = $(this).data('title');
        var id = $(this).data('id');
        bootbox.confirm({
            className: 'modal-danger',
            title: 'Hapus Album ?',
            message: "Apakah anda yakin ingin menghapus <span class='text-info'>"+name+"</span>?",
            buttons: {
                confirm: {
                    label: 'Ya',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'Batal',
                    className: 'btn-default'
                }
            },
            callback: function(result){
                galleryChange(id, 'photo-delete', function(data){
                    if(data.success){
                        location.reload();
                    }
                })
            }
        });
    });

    function galleryChange(id, to, callback){
        $.post(globalUrl.postAdminCustomGalleryJSON+"/"+to+"/"+id, function(data){
            callback(data);
        })
    }
}

function menuPage(){
    var short = $('ol.menu-list').sortable({
        handle: ".drag-handle",
        exclude: ".default-permanent",
        isValidTarget: function  ($item, container) {
            if($item.data('type') != "parent")
                return true;
            else
                return $item.parent("ol")[0] == container.el[0];
        },
        onDrop: function($item, container, _super, event){
            $item.removeClass(container.group.options.draggedClass).removeAttr("style")
            $("body").removeClass(container.group.options.bodyClass)
            var data = short.sortable("serialize").get();
            console.log(data);
            _super($item, container)
        }
    });

    setInterval(function(){
        var data = short.sortable("serialize").get();
        $('.menu-data').val(JSON.stringify(data));
        console.log(JSON.stringify(data))
    }, 1000);

    var pageList = new List('menu-list-page-body', {
        valueNames:['title'],
        page: 3,
        plugins: [ ListPagination({
            outerWindow: 1,
            innerWindow: 1
        }) ]
    });
    $('#menu-add-page-trigger').on('click', function(){
        $('.menu-page-checkbox:checked').each(function(i, elm){
            $('.menu-list').append(Mustache.render($('#menu-page-tmpl').html(), {
                data:{
                    title: $(elm).data('title'),
                    path: $(elm).data('path'),
                    type: 'page',
                    id: $(elm).data('id'),
                    panel: $('.menu-list').find('li').length + 1,
                }
            }))
            $(elm).prop('checked', false);
        })
    });
    $('#menu-add-dropdown-trigger').on('click', function(){
        $('.menu-list').append(Mustache.render($('#menu-dropdown-tmpl').html(), {
            data:{
                title: 'Menu Kebawah',
                pannel: $('.menu-list').find('li').length + 1,
            }
        }))
    });
    window.renameTitle = function (elm){
        var val = $(elm).val();
        $(elm).parent().parent().parent().parent().data('title', val);
        $(elm).parent().parent().parent().find('.menu-title').html(val);
        console.log(val)
    }

    window.removeMenu = function (elm){
        $(elm).parent().parent().parent().remove();
    }
}
