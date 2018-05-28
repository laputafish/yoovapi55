<?php namespace App\Models;

class Media extends BaseModel {

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'medias';

  // custom
  protected $titleField = 'filename';

  public function getPath($imageSize='') {

    $prefixPath = 'temp';
    if ($this->is_image) {
      $prefixPath = 'image';
      if(!empty($imageSize)) {
        $prefixPath .= '_'.$imageSize;
      }
    }
    else if(!$this->is_temp) {
      $prefixPath = 'doc';
    }
    return base_path('storage/app/' . $prefixPath . '/' . $this->path . '/' . $this->filename);
  }

  public function getFileTypeAttribute() {
    $filename = $this->attributes['filename'];
    return strtolower(pathinfo( $filename, PATHINFO_EXTENSION ));
  }

  public function getIsImageAttribute() {
    $ext = $this->getFileTypeAttribute();
    return in_array($ext, ['png','jpg','jpeg','gif']);
  }

  public function isFileType( $type ) {
    $ext = strtolower(
      pathinfo(
        $this->filename, PATHINFO_EXTENSION
      )
    );
    return $type==$ext;
  }
}
