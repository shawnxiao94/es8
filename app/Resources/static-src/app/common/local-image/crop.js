import EsImageCrop from 'common/es-image-crop';
import notify from 'common/notify';

class LocalImageCrop {
  constructor(props) {
    this.cropImg = props.cropImg;
    this.saveBtn = props.saveBtn;
    this.selectBtn = props.selectBtn;
    this.imgs = props.imgs;
    this.group = props.group;
    
    this.uploadInput = props.uploadInput || '.js-upload-input';
    this.sourceImg = props.sourceImg || '.js-source-img';
    this.modal = props.modal || '#modal';

    this.init();
  }

  init() {
    let $sourceImg = $(this.sourceImg);
    this.initImage($sourceImg);

    let imageCrop = this.imageCrop();
    this.initEvent(imageCrop);
  }

  initImage($sourceImg) {
    $(this.cropImg).attr({
      'src': $sourceImg.attr('src'),
      'width': $sourceImg.attr('width'),
      'height': $sourceImg.attr('height'),
      'data-natural-width': $sourceImg.data('natural-width'),
      'data-natural-height': $sourceImg.data('natural-height')
    });

    $sourceImg.remove();
  }

  initEvent(imageCrop) {
    $(this.saveBtn).on('click', event => this.saveEvent(event, imageCrop));
    $(this.selectBtn).on('click', event => this.selectEvent(event));
  }

  saveEvent(event, imageCrop) {
    event.stopPropagation();
    const $this = $(event.currentTarget);
    console.log('start crop')
    imageCrop.crop({
      imgs: this.imgs,
      post: false
    });

    $this.button('loading');
  }

  selectEvent(event) {
    $(this.uploadInput).click();
  }

  imageCrop() {
    let imageCrop = new EsImageCrop({
      element: this.cropImg,
      cropedWidth: this.imgs.large[0],
      cropedHeight: this.imgs.large[1],
      group: this.group
    });

    imageCrop.afterCrop = (res) => {
      this.afterCrop(res);
    }

    return imageCrop;
  }

  afterCrop(cropOptions) {
    let fromData = new FormData();
    let $modal =  $(this.modal);
    let $input = $(this.uploadInput);

    fromData.append('token', $input.data('token'));
    fromData.append('file', $input[0].files[0]);

    let uploadImage = function() {
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: $input.data('fileUpload'),
          type: 'POST',
          cache: false,
          data: fromData,
          processData: false,
          contentType: false,
        }).done(function(data) {
          resolve(data);
        });
      });
    }

    let cropImage = function(res) {
      return new Promise(function(resolve, reject) {
        $.post($input.data('crop'), cropOptions, function(data) {
          resolve(data);
        });
      });
    };

    let saveImage = function(res) {
      return new Promise(function(resolve, reject) {
        $.post($input.data('saveUrl'), { images: res }, function(data) {
          if (data.image) {
            $($input.data('targeImg')).attr('src', data.image);
            notify('success', Translator.trans('site.upload_success_hint'));
          }
        }).error(function() { 
          notify('danger', Translator.trans('site.upload_fail_retry_hint'));
        }).always(function() {
          $input.val('');
          $modal.modal('hide');
        })
      });
    }

    uploadImage().then(function(res) {
      return cropImage(res);

    }).then(function(res) {
      return saveImage(res);

    }).catch(function(res) {
      console.log(res);
    });

  }
}

export default LocalImageCrop;