<?php

//
// Bloxx - Open Source Content Management System
//
// Copyright 2002 - 2005 Telmo Menezes. All rights reserved.
//
// Bloxx is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// Bloxx is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with Bloxx; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//

function scaleJpegWidth($jpg_file, $width)
{

        $src = ImageCreateFromJpeg($jpg_file);
        
        $or_width = imagesx($src);
        $or_height = imagesy($src);
        $ratio = $or_height / $or_width;
        $height = $width * $ratio;
        
        $dst = ImageCreateTrueColor($width, $height);
        ImageCopyResampled($dst, $src, 0, 0, 0, 0, $width, $height, $or_width, $or_height);
        
        ob_start();
        ImageJpeg($dst, '', 91);
        $buffer = ob_get_contents();
        ob_end_clean();
        
        ImageDestroy($src);
        ImageDestroy($dst);
        
        return $buffer;
}

function scaleJpegHeight($jpg_file, $height)
{

        $src = ImageCreateFromJpeg($jpg_file);

        $or_width = imagesx($src);
        $or_height = imagesy($src);
        $ratio = $or_width / $or_height;
        $width = $height * $ratio;

        $dst = ImageCreateTrueColor($width, $height);
        ImageCopyResampled($dst, $src, 0, 0, 0, 0, $width, $height, $or_width, $or_height);

        ob_start();
        ImageJpeg($dst, '', 91);
        $buffer = ob_get_contents();
        ob_end_clean();

        ImageDestroy($src);
        ImageDestroy($dst);

        return $buffer;
}

function getJpegWidth($jpg_file)
{

        $src = ImageCreateFromJpeg($jpg_file);

        return imagesx($src);
}

function getJpegHeight($jpg_file)
{

        $src = ImageCreateFromJpeg($jpg_file);

        return imagesy($src);
}
?>
