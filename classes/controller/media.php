<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Media class
 *
 * @package    Kohana/Media
 * @category   Controllers
 * @author     Birkir Rafn Gudjonsson
 * @copyright  (c) 2010 BRG
 * @license    http://kohanaphp.com/license.html
 */
class Controller_Media extends Controller {

	// Gzip output
	private $gzip = TRUE;

	/**
	 * Process the file
	 *
	 * @param   string  filename
	 * @return  void
	 */
	public function action_process($filename = NULL)
	{
		// Initialize Media
		$media = Media::factory()
		->load($filename);

		if ($media !== FALSE)
		{
			// Smush.it png, gif and jpg files
			if (in_array($media->ext, array('png', 'gif', 'jpg', 'jpeg')))
			{
				$media = $media->smushit();
			}

			// Minify js and css files
			if (in_array($media->ext, array('js', 'css')))
			{
				$media = $media->minify();
			}

			// Gzip files if flagged
			if ($this->gzip === TRUE)
			{
				$media = $media->gzip();
				$this->response->headers('content-encoding', 'gzip');
			}

			// Set response body and headers
			$this->response->check_cache(sha1($this->request->uri()).filemtime($media->file), $this->request);
			$this->response->body($media->render());
			$this->response->headers('content-type',  File::mime_by_ext($media->ext));
			$this->response->headers('last-modified', date('r', filemtime($media->file)));
		}
		else
		{
			$this->response->status(404);
		}
	}
}
