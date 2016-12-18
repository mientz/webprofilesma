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
                        elm.summernote('insertImage', "public/content/"+image)
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
            },
            onInit: function () {
                $(textElm).val($('#summernote').html());
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
                    callback(true, dialog.find('.global-postmedia-image-list option:selected').text());
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
        globalPopulatePostmediaFilterDateInput(function(html){
            dialog.find('.global-postmedia-image-list-short-date').html(html)
        });
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

        dialog.find('.global-postmedia-image-list-short-date').on('change', function(){
            pospulateImageOnTab($(this).val());
        })

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

/*
 * ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 * ################################################################################################################################################
 * %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 */

/**
 * run When Postmedia Page is Load
 */
function postmediaPage(){
    $('.postmedia-image-uploader').dropzone({
        url: globalUrl.postAdminPostmediaUploadJSON,
        maxFileSize: 2,
        createImageThumbnails: true,
        aceptedFiles: 'image/*,',
        previewTemplate: Mustache.render($('#postmedia-upload-preview-tmpl').html()),
        clickable: true,
        init: function () {
            this.on('complete', function (file) {
                this.removeFile(file)
                $('#collapse-upload').collapse('hide');
                globalPopulatePostmediaFilterDateInput(function(html){
                    dialog.find('#postmedia-select-date').html(html)
                });
            })
        }
    });

    globalPopulatePostmediaFilterDateInput(function(html){
        $('#postmedia-select-date').html(html)
    });

    globalPopulatePostmediaList('all', function(data){
        $('#postmedia-list-images').html(Mustache.render($('#postmedia-list-upload-preview-tmpl').html(), data));
    })

    $('#postmedia-select-date').on('change', function(){
        globalPopulatePostmediaList($(this).val(), function(data){
            $('#postmedia-list-images').html(Mustache.render($('#postmedia-list-upload-preview-tmpl').html(), data));
        })
    });

    function postmediaDetailExifDialog(id){

    }

    $('#postmedia-list-images').on('click', '.postmedia-image-selector', function(){
        globalPostmediaDetailExif($(this).data('id'), function(data){
            var dialog = bootbox.dialog({
                title: 'Detil Media',
                size: 'large',
                message:Mustache.render($('#postmedia-image-detail-preview').html(), data),
                onEscape: true,
                backdrop: true,
            });
            dialog.init(function(){
                dialog.find('.postmedia-image-delete-trigger').on('click', function(){
                    bootbox.confirm({
                        title: "Hapus Gambar Permanen ?",
                        message: "<img src='" + globalUrl.getAdminPostmediaThumbnailIMAGE + '/' + data.postmedia.file+"' width='100%'>",
                        size: 'small',
                        buttons: {
                            cancel: {
                                label: '<i class="fa fa-times"></i> Batal'
                            },
                            confirm: {
                                label: '<i class="fa fa-check"></i> Hapus'
                            }
                        },
                        callback: function (result) {
                            if(result){
                                $.getJSON(globalUrl.getAdminPostmediaDeleteJSON + "/" + data.postmedia.id, function(data){
                                    if(data.status=="success"){
                                        globalPopulatePostmediaFilterDateInput(function(html){
                                            $('#postmedia-select-date').html(html)
                                        });
                                        globalPopulatePostmediaList('all', function(data){
                                            $('#postmedia-list-images').html(Mustache.render($('#postmedia-list-upload-preview-tmpl').html(), data));
                                        })
                                        dialog.modal('hide')
                                    }else{
                                        bootbox.alert(data.cause);
                                    }
                                })
                            }
                        }
                    });
                })
            })
        })
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
        width: imageCropper.parent().parent().width()/1.5,
        height: imageCropper.parent().parent().width()/4,
        allowDragNDrop:false,
        smallImage: 'allow',
        minZoom: 'fill',
        onImageError: function(error){
            alert(error);
        },
        onImageLoaded: function(){
            $.getJSON(globalUrl.getAdminPostmediaCropitJson + '/' + post_data.header_image, function(data){
                if(data.image != 'no-image'){
                    imageCropper.cropit('previewSize', {
                        width:parseFloat(data.width),
                        height:parseFloat(data.height)
                    });
                    imageCropper.cropit('zoom', parseFloat(data.zoom));
                    imageCropper.cropit('offset', {
                        x: parseFloat('-'+data.width),
                        y: parseFloat('-'+data.height)
                    });
                }else{

                }
            });
        }
    });

    //tag input init and populate tags typeahead source
    $('#edit-post-tags-input').tagsinput({
        typeahead:{
            source: function(query) {
                return $.get(globalUrl.getAdminTagsListJSON, function(data){
                    return data;
                }, 'json');
            }
        },
        freeInput: true
    });

    // populate image header data
    $.getJSON(globalUrl.getAdminPostmediaCropitJson + '/' + post_data.header_image, function(data){
        if(data.image != 'no-image'){
            imageCropper.cropit('imageSrc', 'public/content/'+data.image);
        }
    });

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
                imageCropper.cropit('imageSrc', 'public/content/'+image);
            }else{
                $('#edit-post-header').addClass('hidden');
            }
        })
    })

    // actions on image header change
    $('#edit-post-header-select-change').on('click', function(){
        globalPostmediaSelector("Ganti Gambar Fitur", function(is_picked, image){
            if(is_picked){
                imageCropper.cropit('imageSrc', 'public/content/'+image);
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
            $('#add-post-header-value').val(
                parseInt(data.size.width)+'/'+parseInt(data.size.height)+'/'+Math.abs(parseInt(data.offset.x))+'/'+Math.abs(parseInt(data.offset.y))+'/'+data.zoom+'/'+data.image
            );
        },
        onOffsetChange: function(offset){
            var data = {
                image: imageCropper.data('image'),
                size: imageCropper.cropit('previewSize'),
                offset: offset,
                zoom: imageCropper.cropit('zoom'),
            }
            localStorage.imageCroped = JSON.stringify(data);
            $('#add-post-header-value').val(
                parseInt(data.size.width)+'/'+parseInt(data.size.height)+'/'+Math.abs(parseInt(data.offset.x))+'/'+Math.abs(parseInt(data.offset.y))+'/'+data.zoom+'/'+data.image
            );
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
                $('#add-post-header-value').val(
                    parseInt(data.size.width)+'/'+parseInt(data.size.height)+'/'+Math.abs(parseInt(data.offset.x))+'/'+Math.abs(parseInt(data.offset.y))+'/'+data.zoom+'/'+data.image
                );
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
                imageCropper.cropit('imageSrc', 'public/content/'+image);
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
                imageCropper.cropit('imageSrc', 'public/content/'+image);
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
        if(typeof $('#add-post-form').data('id') === 'undefined'){
            e.preventDefault();
            SavePostChange('#add-post-form', 'new', '', function(res){
                $('#add-post-form').data('id', res.id);
            }, true);
        }
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
    }, 1000);
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

