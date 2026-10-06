<?php
namespace Modules\Admin\Services\Others;

use App\Models\Slider;
use App\Models\SliderImage;
use App\Traits\HasPublicId;
use Illuminate\Http\UploadedFile;
use Modules\Admin\Repositories\Others\SliderImageRepository;

class SliderImageService
{
    use HasPublicId;

    public function __construct(protected SliderImageRepository $sliderImageRepository){}

    public function getSliderByPublicId(string $publicId): Slider
    {
        $sliderId=(int)$this->decode($publicId);
        return Slider::findOrFail($sliderId);
    }

    public function getSliderImages(int $sliderId, int $perPage = 30, ?string $type = null)
    {
        return $this->sliderImageRepository->paginate($sliderId, $perPage, $type);
    }

     public function create(int $sliderId,array $data,?UploadedFile $image=null):SliderImage
    {

        if($image){
            $fileName=$image->hashName();

            $image->move(
                public_path('uploads/slider'),
                $fileName
            );

            $data['image_name']='uploads/slider/'.$fileName;
        }

        $data['slider_id']=$sliderId;

        return $this->sliderImageRepository->create($data);
    }

    public function getById(int $id): SliderImage
    {
        return $this->sliderImageRepository->find($id);
    }

    public function getByPublicId(string $publicId): SliderImage
    {
        $id = (int) $this->decode($publicId);

        return $this->sliderImageRepository->find($id);
    }

    public function updateByPublicId(string $publicId, array $data,
            Request $request
        ): SliderImage {
            $sliderImage = $this->getByPublicId($publicId);

            if ($request->hasFile('image')) {
                $file = $request->file('image');

                $fileName = time() . '_' . $file->getClientOriginalName();

                $file->move(
                    public_path('uploads/slider'),
                    $fileName
                );

                $data['image_name'] = 'uploads/slider/' . $fileName;
            }

            unset($data['image']);

            $sliderImage->update($data);

            return $sliderImage->fresh();
        }

    public function update(SliderImage $model, array $data,?UploadedFile $file = null): SliderImage
    {

        if ($file) {
            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->move(
                public_path('uploads/slider'),
                $fileName
            );

            $data['image_name'] = 'uploads/slider/' . $fileName;
        }

        $model->update($data);

        return $model->fresh();
    }
    
    public function getSliderById(int $id): Slider
    {
        return Slider::findOrFail($id);
    }

    public function delete(SliderImage $model): bool
    {
        return $this->sliderImageRepository->delete($model);
    }


    /**
     * Update slider image status.
     */
    public function updateStatus( string $imagePublicId, int $status): SliderImage {

        $image = $this->getByPublicId($imagePublicId);

        $image->status = $status;

        $image->save();

        return $image->fresh();   
    }
}