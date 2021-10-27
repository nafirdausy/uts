<?php

namespace App\Http\Livewire;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

use Livewire\Component;
use App\Models\Location;

class MapLocation extends Component
{

    use WithFileUploads;

    public $long,$lat,$title,$description,$image;
    public $geoJson;
    public $imageUrl;
    
    private function loadLocations(){
        $locations = Location::orderBy('created_at', 'desc')->get();
        
        $customLocations =[];

        foreach($locations as $location){
            $customLocations[] = [
                'type' => 'Feature',
                'geometry' => [
                    'coordinates' => [$location->long, $location->lat],
                    'type' => 'Point'
                ],
                'properties' => [
                    'locationId' => $location->id,
                    'title' => $location->title,
                    'image' => $location->image,
                    'description' => $location->description

                ]
            ];
        }
        $geoLocation = [
            'type' => 'FeatureCollection',
            'features' => $customLocations
        ];

        $geoJson = collect($geoLocation)->toJson();
        $this->geoJson =$geoJson;
    }

    private function clearForm(){
            $this->long= '';
            $this->lat= '';
            $this->title= '';
            $this->description= '';
            $this->image = '';
    }

    public function saveLocation(){
        $this->validate([
            'long' => 'required',
            'lat' => 'required',
            'title' => 'required',
            'description' => 'required',
            'image' => 'image|max:2048|required',
        ]);
    $imageName = md5($this->image.microtime()).'.'.$this->image->extension();

    Storage::putFileAs(
        'public/images',
        $this->image,
        $imageName
    );

    Location::create([
            'long' => $this->long,
            'lat' => $this->lat,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $imageName,
    ]);

    $this->clearForm();
    $this->loadLocations();
    $this->dispatchBrowserEvent('locationAdded', $this->geoJson);
    }

    public function render()
    {
        $this->loadLocations();
        return view('livewire.map-location');
    }
}
