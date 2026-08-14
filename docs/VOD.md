# VOD

This document explains how to enable, configure, and use the VOD module.

## Overview

The VOD module allows you to:

- Create videos with title, description, and file.
- Edit and replace the video file.
- List videos in the admin panel.
- Play videos through web and API routes.

## 1) Enable the module

In your .env file, set:

MODULE_VOD_ENABLED=true

When set to false, the menu and dashboard entry are hidden, the VOD web and API routes return 404, and M3U playlists do not query or include VOD entries.

## 2) Environment variables

Add to .env:

VOD_DISK=vod-master
VOD_MAX_UPLOAD_SIZE=10485760

Meaning:

- VOD_DISK: storage disk used to save files.
- VOD_MAX_UPLOAD_SIZE: maximum upload size in KB.

Example: 10485760 KB is approximately 10 GB.

## 3) Storage configuration

By default, the vod-master disk exists in config/filesystems.php:

- driver: local
- root: storage/app/vod/master
- visibility: private

Important:

- Video files are not publicly exposed by direct URL.
- Playback is served by a controller that reads the file and returns an inline response.

## 4) Upload rules

Rules are defined in:

- StoreVodRequest
- UpdateVodRequest

Current validations:

- name is required
- description is optional
- file is required on create
- file is optional on update
- allowed mime types come from config/vod.php
- max size comes from VOD_MAX_UPLOAD_SIZE

## 5) Web routes (admin panel)

With the module enabled:

- GET /vods/list
- GET /vods/new
- POST /vods/new
- GET /vods/edit/{id}
- POST /vods/edit/{id}
- GET /vods/play/{id}
- DELETE /vods/delete/{id}

## 6) API routes

The public catalog and playback API.

- GET /api/vods
- GET /api/vods/{id}
- GET /api/vods/{id}/play

The {id} parameter accepts numeric id, slug, or uuid.

The `per_page` query parameter is limited to values between 1 and 100.

## 7) File storage flow

When saving a video:

1. The record is created/updated in the iptv_vods table.
2. The file is saved at:

	vod/{uuid}/video.{extensao}

3. Persisted metadata:

- disk
- path
- original_filename
- mime_type
- file_size

When replacing a file, the new file is stored and its metadata is persisted before the old file is removed. If storing or persisting the new file fails, the old file remains active and the new file is removed.

## 8) Database

Table: iptv_vods

Main columns:

- uuid
- name
- slug
- description
- disk
- path
- original_filename
- mime_type
- file_size
- timestamps

## 9) Enable in an existing environment

Recommended step by step:

1. Update .env:

- MODULE_VOD_ENABLED=true
- VOD_DISK=vod-master
- VOD_MAX_UPLOAD_SIZE=10485760

2. Run migrations:

php artisan migrate

3. Clear config and route cache:

php artisan optimize:clear

4. Validate in the admin panel that the VOD menu appears.

## 10) Operational tips

- If you change VOD_DISK, ensure the disk exists in config/filesystems.php.
- If using cloud storage (for example, S3), adjust driver, credentials, and visibility on the selected disk.
- Since the module serves inline responses, keep the mime type correct for better player compatibility.
