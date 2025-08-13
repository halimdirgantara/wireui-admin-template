How to use Quill Editor with Laravel 10 and Livewire v3
#
quill
#
laravel
#
livewire
#
php
I wanted to build a content management system for a project I was working on recently and I needed to use a rich text editor. I tried so many rich text editors but most didn't just work well with the requirements given to by the client.

Handling image upload on Trix editor was difficult to implement, I couldn't find any rich text editor to help with that until I stumbled on Quill rich text editor.

I will not go into the details of how to install Laravel 10 and Livewire v3 because I will want to assume this particular implementation is for those who are mid to senior developers.

First step:
In your Layout folder e.g. resources/views/layouts/app.blade.php insert these Quill's CDN in your head block

<head>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.5/dist/quill.snow.css" rel="stylesheet" />

</head>
Note: the second CDN is a theme.

Second step:
In your script insert this script CDN

<script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.5/dist/quill.js"></script>
</script>
Then the next step will be to create a livewire component using

php artisan make:livewire CreateBlogPost 
This will create two files in the resources/views/livewire and app/Livewire folders.

Third step:
Open the CreateBlogPost.php in the app/Livewire folder and insert these blocks of Code.

use Intervention\Image\ImageManagerStatic as Image;

use WithFileUploads;

public $title;
public $trixId;
public $photos = [];
public $cover_image;
public $content = '';
public $tags;
public $imageNames = [];

public function uploadImage($image)
    {
        $imageData = substr($image, strpos($image, ',') + 1);

        $length = strlen($imageData);
        $lastSixCharacters = substr($imageData, $length - 20);

        $imageData = base64_decode($imageData);

        $filename = $lastSixCharacters . '.png';

        $resizedImage = Image::make($imageData)->resize(null, 400, function ($constraint) {
            $constraint->aspectRatio();
        });

        Storage::disk('public_uploads')->put('/blog_photos/' . $filename, $resizedImage->encode());

        $url = url('/files/blog_photos/' . $filename);

        $this->content .= '<img style="" src="' . $url . '" alt="Uploaded Image">';
        return $this->dispatch('blogimageUploaded', $url);
    }
This code block is receiving image(s) in base64 so we need to decode it and convert it to a file we can save. Then we will resize the image so it will suit the purpose of what we need.

We will then store it in a public folder (my preference), get the URL of the image uploaded and pass it in an HTML-like way, and dispatch it to a listener in our view file.

We will then insert this block of code right below uploadImage function.

public function deleteImage($image)
    {
        $imageData = substr($image, strpos($image, ',') + 1);

        $length = strlen($imageData);

        $lastSixCharacters = substr($imageData, $length - 20);

        $filename = $lastSixCharacters . '.png';

        if (file_exists(public_path("files/blog_photos/" . $filename))) {
            unlink(public_path("files/blog_photos/" . $filename));
        }
    }
This function will be dispatched from the script section of create-blog-post blade file.

Then we insert this code block right underneath deleteImage

public function submitBlogPost()
    {
        $this->validate();

        $cover_photo = uniqid() . '.' . $this->cover_image->extension();
        $this->cover_image->storeAs('blog_cover_photo', $cover_photo, 'public_uploads');

        $blog = Blog::create([
            'title' => $this->title,
            'cover_image' => $cover_photo,
            'body' => $this->content,
            'tags' => $this->tags,
            'slug' => Str::slug($this->title)
        ]);

        return $this->dispatch('notify', 'Blog post created successfully', 'Success', 'success');
    }
This will submit the blog post, straightforward for an average Laravel dev.

Now we have come full circle on the functions required to create a blog post and Image upload in app/Livewire folder, now let's move to the view part.

Fourth step:
Goto to your resources/views/livewire/create-blog-post.blade.php file, then insert this code blocks

<div class="relative mt-4" wire:ignore>
      <label for="default-search" class="mb-2 text-sm font-medium text-gray-900">Body</label>
      <div id="editor" wire:model="content"></div>
    </div>
<script>
  var editor = new Quill('#editor', {
      theme: 'snow',
      modules: {
          toolbar: [
              ['bold', 'italic', 'underline'],
              [{ 'header': 1 }, { 'header': 2 }],
              [{ 'list': 'ordered'}, { 'list': 'bullet' }],
              ['image', 'link'],
              ['align', { 'align': 'center' }],
              ['clean']
          ]
      }
  });

  editor.getModule('toolbar').addHandler('image', function () {
      @this.set('content', editor.root.innerHTML);

      var input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.setAttribute('accept', 'image/*');
      input.click();

      input.onchange = function () {
          var file = input.files[0];
          if (file) {
              var reader = new FileReader();

            reader.onload = function(event) {
                var base64Data = event.target.result;

                @this.uploadImage(base64Data);
            };
            // Read the file as a data URL (base64)
            reader.readAsDataURL(file);
          }
      };
  });

  let previousImages = [];

  editor.on('text-change', function(delta, oldDelta, source) {
      var currentImages = [];

      var container = editor.container.firstChild;

      container.querySelectorAll('img').forEach(function(img) {
          currentImages.push(img.src);
      });

      var removedImages = previousImages.filter(function(image) {
          return !currentImages.includes(image);
      });

      removedImages.forEach(function(image) {
          @this.deleteImage(image);
          console.log('Image removed:', image);
      });

      // Update the previous list of images
      previousImages = currentImages;
  });

  Livewire.on('blogimageUploaded', function(imagePaths) {
    if (Array.isArray(imagePaths) && imagePaths.length > 0) {
        var imagePath = imagePaths[0]; // Extract the first image path from the array
        console.log('Received imagePath:', imagePath);

        if (imagePath && imagePath.trim() !== '') {
            var range = editor.getSelection(true);
            editor.insertText(range ? range.index : editor.getLength(), '\n', 'user');
            editor.insertEmbed(range ? range.index + 1 : editor.getLength(), 'image', imagePath);
        } else {
            console.warn('Received empty or invalid imagePath');
        }
    } else {
        console.warn('Received empty or invalid imagePaths array');
    }
  });
});
</script>
In this code block, we create the HTML div where Quill editor will be loaded/referenced, notice the wire:ignore tag in the container of the div, this is to make sure whenever Livewire is updated, the Quill div will be ignored (will not update so our changes will not be cleared).

Now to the script part. The first part of the script initializes quill for the for the HTML and passes the toolbar required, you can add more functionality.

Then, the event listener called text-change lets us listen to changes we make in our content model in the quill editor, it allows us to set the change to our model anytime we type or make changes in the quill editor. We can also listen to deleted images, so once an image is deleted, we will dispatch the livewire deleteImage function.

Then the event listener referencing toolbar also allows us to listen to changes in images uploaded, notice that images are converted to base64 and are sent to the the livewire function called uploadImage.

Then the last listener which is a Livewire listener blogimageUploaded helps us handle images uploaded. It helps us to insert the image uploaded and its path from the uploadImage i.e

 $this->content .= '<img style="" src="' . $url . '" alt="Uploaded Image">';

return $this->dispatch('blogimageUploaded', $url);
Notice the img attribute and the URL being passed to the event dispatch from blogimageUploaded.

If you follow these steps, it will be a smooth ride making use of Quill rich editor in Laravel 10 and Livewire v3 application.

If you need clarity, kindly drop your questions.

Cheers!
