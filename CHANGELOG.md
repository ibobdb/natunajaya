# Changelog

## [1.3.5](https://github.com/ibobdb/natunajaya/compare/v1.3.4...v1.3.5) (2025-05-25)


### Bug Fixes

* update PHP-FPM configuration to use TCP instead of Unix socket ([fdafd56](https://github.com/ibobdb/natunajaya/commit/fdafd56cd00b9f16023961c24b251b8877176720))

## [1.3.4](https://github.com/ibobdb/natunajaya/compare/v1.3.3...v1.3.4) (2025-05-25)


### Bug Fixes

* update PHP-FPM version in nginx configuration ([fdd3e95](https://github.com/ibobdb/natunajaya/commit/fdd3e950eb5102afd44977e88afc14c8aa4d4a91))


### Chores

* add PHP-FPM and nginx programs to supervisord configuration ([fdd3e95](https://github.com/ibobdb/natunajaya/commit/fdd3e950eb5102afd44977e88afc14c8aa4d4a91))

## [1.3.3](https://github.com/ibobdb/natunajaya/compare/v1.3.2...v1.3.3) (2025-05-25)


### Bug Fixes

* update supervisor configuration path in Dockerfile ([b1704d8](https://github.com/ibobdb/natunajaya/commit/b1704d8838e2dd8992293df78837f452a6bd28be))

## [1.3.2](https://github.com/ibobdb/natunajaya/compare/v1.3.1...v1.3.2) (2025-05-25)


### Bug Fixes

* update Docker login action to use secrets for credentials ([a650529](https://github.com/ibobdb/natunajaya/commit/a6505298adfef5e00cce7be96b77a1999b3e4a9d))


### Chores

* update docker-build workflow to remove push trigger ([c2f3d4d](https://github.com/ibobdb/natunajaya/commit/c2f3d4d02aa8567d2f1ae3acca03a6741d4113f3))
* update workflow trigger for Docker build ([2ec6a29](https://github.com/ibobdb/natunajaya/commit/2ec6a2998136846854e4ca34e43b8cac97014806))

## [1.3.1](https://github.com/ibobdb/natunajaya/compare/v1.3.0...v1.3.1) (2025-05-25)


### Bug Fixes

* update Docker login action to use environment variables for credentials ([9d040ef](https://github.com/ibobdb/natunajaya/commit/9d040efb5e3b6053abb3e8edc388fbfeac4f6a49))

## [1.3.0](https://github.com/ibobdb/natunajaya/compare/v1.2.0...v1.3.0) (2025-05-25)


### Features

* enhance authentication views with new layout and styles ([99cb892](https://github.com/ibobdb/natunajaya/commit/99cb892060e094b64da6f3b44e5bbf0159ca9b7f))
* implement schedule management features ([608e250](https://github.com/ibobdb/natunajaya/commit/608e2503c42cf071fadc71d4e1f3ee8cce7a2cd3))
* update Tailwind CSS configuration with new font families, colors, box shadows, and border colors ([655afe3](https://github.com/ibobdb/natunajaya/commit/655afe3d22b2709afdb59e45df79f6747684d729))

## [1.2.0](https://github.com/ibobdb/natunajaya/compare/v1.1.0...v1.2.0) (2025-05-23)


### Features

* add Docker build and push workflow ([f5d908f](https://github.com/ibobdb/natunajaya/commit/f5d908f14f3bca866ba259e025dcc84288e72b20))
* add Nginx configuration for Docker setup ([0bf3c0e](https://github.com/ibobdb/natunajaya/commit/0bf3c0e6ddad2fb61713595a5262dab6314ef058))
* configure Supervisor for Docker setup ([d0f48db](https://github.com/ibobdb/natunajaya/commit/d0f48db57643a3f6b0e96a8f82813b18d1c74348))
* install additional PHP extensions in Dockerfile ([63f4c68](https://github.com/ibobdb/natunajaya/commit/63f4c681e28a8d8c00af909f48008a01a9c244d3))
* update Docker build process to use GIT_TAG and configure Nginx ([149238a](https://github.com/ibobdb/natunajaya/commit/149238affbd56c2be7edd74d283768bedaa30321))


### Code Refactoring

* update PHP extensions installation in Dockerfile ([4b2303d](https://github.com/ibobdb/natunajaya/commit/4b2303dab3111fd4ec2cd14e54a6e656d3f059ae))

## [1.1.0](https://github.com/ibobdb/natunajaya/compare/v1.0.0...v1.1.0) (2025-05-23)


### Features

* add car management to schedule resource ([ddcf86f](https://github.com/ibobdb/natunajaya/commit/ddcf86fd3092be5672ecf6e47215c5f04ad9546f))
* Add Car, Course, Instructor, and Schedule resources with management pages ([db3cfda](https://github.com/ibobdb/natunajaya/commit/db3cfda756d7b73f9d00c10023f7214b66bbd0e5))
* add course and order management features ([f53f46e](https://github.com/ibobdb/natunajaya/commit/f53f46e347e4c3acfc9d7a303ac447bf90a90947))
* add payment record URL to order management table and comment out payment notification route ([729840e](https://github.com/ibobdb/natunajaya/commit/729840e65d1ed3cbfca15ec6a18edd699a97e556))
* add ScheduleResource and ManageSchedules page for student schedule management ([ad89823](https://github.com/ibobdb/natunajaya/commit/ad8982315e2f373e5975ca288399bdeb3b1717bc))
* add student role assignment and student entry creation on registration ([718941d](https://github.com/ibobdb/natunajaya/commit/718941d25cfdb93867392e0864d826497be1747d))
* enhance Midtrans callback handling and add invoice_id to student_course ([0bb662e](https://github.com/ibobdb/natunajaya/commit/0bb662e58f2fda60ae3b013228e345257196270b))
* enhance order creation form and add schedule and car ID handling ([6acf403](https://github.com/ibobdb/natunajaya/commit/6acf403d2404deb9e9d8c393c07985b6527b61c8))
* implement payment page and order redirection ([dd6d9ea](https://github.com/ibobdb/natunajaya/commit/dd6d9ea37e6bf32e89e655cc27f5a31a97cd28a6))
* implement role-based redirection in dashboard route ([4c832a7](https://github.com/ibobdb/natunajaya/commit/4c832a781d36ef9ad991f1752591ac91575d4fee))
* implement user role-based access for admin and users panel ([8be5e96](https://github.com/ibobdb/natunajaya/commit/8be5e96261481979529ba809cd6dd5c6d455be46))
* integrate Midtrans payment gateway and implement payment processing ([95d40d6](https://github.com/ibobdb/natunajaya/commit/95d40d60da36d2407dcfde89b0b5a4a7c0cd4d1e))


### Chores

* **master:** release 1.0.0 ([b9452f3](https://github.com/ibobdb/natunajaya/commit/b9452f3eb92876cce361495b7f5ecbd34e78b447))
* remove Docker build workflow ([3e32718](https://github.com/ibobdb/natunajaya/commit/3e327181546e60bb2c123273c12f3b6bee8b0f6e))


### Code Refactoring

* improve order management table and enhance action visibility ([971ab40](https://github.com/ibobdb/natunajaya/commit/971ab4037dbc72205b40d7cf16351b137b71fd1e))
* improve ScheduleResource navigation properties and add badge functionality ([be537a8](https://github.com/ibobdb/natunajaya/commit/be537a8a6fb2da63d7d0e3ce86e61caa765bf08f))
* remove edit and delete actions from course management table ([e8c6a7e](https://github.com/ibobdb/natunajaya/commit/e8c6a7e8d8bada4ac27e4cd0fcd70800d37d5d71))
* Remove unused resources and models related to cars, courses, orders, schedules, and teachers ([e677f9d](https://github.com/ibobdb/natunajaya/commit/e677f9d29d6bcae13d2b680f0d070a51cd51bc64))
* update ScheduleResource form and table for improved usability ([223c6cc](https://github.com/ibobdb/natunajaya/commit/223c6cc6a7add8114ac76c79e187ba7e2f0b18d2))

## 1.0.0 (2025-05-23)


### Features

* add car management to schedule resource ([ddcf86f](https://github.com/ibobdb/natunajaya/commit/ddcf86fd3092be5672ecf6e47215c5f04ad9546f))
* Add Car, Course, Instructor, and Schedule resources with management pages ([db3cfda](https://github.com/ibobdb/natunajaya/commit/db3cfda756d7b73f9d00c10023f7214b66bbd0e5))
* add course and order management features ([f53f46e](https://github.com/ibobdb/natunajaya/commit/f53f46e347e4c3acfc9d7a303ac447bf90a90947))
* add payment record URL to order management table and comment out payment notification route ([729840e](https://github.com/ibobdb/natunajaya/commit/729840e65d1ed3cbfca15ec6a18edd699a97e556))
* add ScheduleResource and ManageSchedules page for student schedule management ([ad89823](https://github.com/ibobdb/natunajaya/commit/ad8982315e2f373e5975ca288399bdeb3b1717bc))
* add student role assignment and student entry creation on registration ([718941d](https://github.com/ibobdb/natunajaya/commit/718941d25cfdb93867392e0864d826497be1747d))
* enhance Midtrans callback handling and add invoice_id to student_course ([0bb662e](https://github.com/ibobdb/natunajaya/commit/0bb662e58f2fda60ae3b013228e345257196270b))
* enhance order creation form and add schedule and car ID handling ([6acf403](https://github.com/ibobdb/natunajaya/commit/6acf403d2404deb9e9d8c393c07985b6527b61c8))
* implement payment page and order redirection ([dd6d9ea](https://github.com/ibobdb/natunajaya/commit/dd6d9ea37e6bf32e89e655cc27f5a31a97cd28a6))
* implement role-based redirection in dashboard route ([4c832a7](https://github.com/ibobdb/natunajaya/commit/4c832a781d36ef9ad991f1752591ac91575d4fee))
* implement user role-based access for admin and users panel ([8be5e96](https://github.com/ibobdb/natunajaya/commit/8be5e96261481979529ba809cd6dd5c6d455be46))
* integrate Midtrans payment gateway and implement payment processing ([95d40d6](https://github.com/ibobdb/natunajaya/commit/95d40d60da36d2407dcfde89b0b5a4a7c0cd4d1e))


### Chores

* **master:** release 1.0.0 ([b9452f3](https://github.com/ibobdb/natunajaya/commit/b9452f3eb92876cce361495b7f5ecbd34e78b447))
* remove Docker build workflow ([3e32718](https://github.com/ibobdb/natunajaya/commit/3e327181546e60bb2c123273c12f3b6bee8b0f6e))


### Code Refactoring

* improve order management table and enhance action visibility ([971ab40](https://github.com/ibobdb/natunajaya/commit/971ab4037dbc72205b40d7cf16351b137b71fd1e))
* improve ScheduleResource navigation properties and add badge functionality ([be537a8](https://github.com/ibobdb/natunajaya/commit/be537a8a6fb2da63d7d0e3ce86e61caa765bf08f))
* remove edit and delete actions from course management table ([e8c6a7e](https://github.com/ibobdb/natunajaya/commit/e8c6a7e8d8bada4ac27e4cd0fcd70800d37d5d71))
* Remove unused resources and models related to cars, courses, orders, schedules, and teachers ([e677f9d](https://github.com/ibobdb/natunajaya/commit/e677f9d29d6bcae13d2b680f0d070a51cd51bc64))
* update ScheduleResource form and table for improved usability ([223c6cc](https://github.com/ibobdb/natunajaya/commit/223c6cc6a7add8114ac76c79e187ba7e2f0b18d2))

## 1.0.0 (2025-05-23)


### Features

* add car management to schedule resource ([ddcf86f](https://github.com/ibobdb/natunajaya/commit/ddcf86fd3092be5672ecf6e47215c5f04ad9546f))
* Add Car, Course, Instructor, and Schedule resources with management pages ([db3cfda](https://github.com/ibobdb/natunajaya/commit/db3cfda756d7b73f9d00c10023f7214b66bbd0e5))
* add course and order management features ([f53f46e](https://github.com/ibobdb/natunajaya/commit/f53f46e347e4c3acfc9d7a303ac447bf90a90947))
* add payment record URL to order management table and comment out payment notification route ([729840e](https://github.com/ibobdb/natunajaya/commit/729840e65d1ed3cbfca15ec6a18edd699a97e556))
* add ScheduleResource and ManageSchedules page for student schedule management ([ad89823](https://github.com/ibobdb/natunajaya/commit/ad8982315e2f373e5975ca288399bdeb3b1717bc))
* add student role assignment and student entry creation on registration ([718941d](https://github.com/ibobdb/natunajaya/commit/718941d25cfdb93867392e0864d826497be1747d))
* enhance Midtrans callback handling and add invoice_id to student_course ([0bb662e](https://github.com/ibobdb/natunajaya/commit/0bb662e58f2fda60ae3b013228e345257196270b))
* enhance order creation form and add schedule and car ID handling ([6acf403](https://github.com/ibobdb/natunajaya/commit/6acf403d2404deb9e9d8c393c07985b6527b61c8))
* implement payment page and order redirection ([dd6d9ea](https://github.com/ibobdb/natunajaya/commit/dd6d9ea37e6bf32e89e655cc27f5a31a97cd28a6))
* implement role-based redirection in dashboard route ([4c832a7](https://github.com/ibobdb/natunajaya/commit/4c832a781d36ef9ad991f1752591ac91575d4fee))
* implement user role-based access for admin and users panel ([8be5e96](https://github.com/ibobdb/natunajaya/commit/8be5e96261481979529ba809cd6dd5c6d455be46))
* integrate Midtrans payment gateway and implement payment processing ([95d40d6](https://github.com/ibobdb/natunajaya/commit/95d40d60da36d2407dcfde89b0b5a4a7c0cd4d1e))


### Chores

* remove Docker build workflow ([3e32718](https://github.com/ibobdb/natunajaya/commit/3e327181546e60bb2c123273c12f3b6bee8b0f6e))


### Code Refactoring

* improve order management table and enhance action visibility ([971ab40](https://github.com/ibobdb/natunajaya/commit/971ab4037dbc72205b40d7cf16351b137b71fd1e))
* improve ScheduleResource navigation properties and add badge functionality ([be537a8](https://github.com/ibobdb/natunajaya/commit/be537a8a6fb2da63d7d0e3ce86e61caa765bf08f))
* remove edit and delete actions from course management table ([e8c6a7e](https://github.com/ibobdb/natunajaya/commit/e8c6a7e8d8bada4ac27e4cd0fcd70800d37d5d71))
* Remove unused resources and models related to cars, courses, orders, schedules, and teachers ([e677f9d](https://github.com/ibobdb/natunajaya/commit/e677f9d29d6bcae13d2b680f0d070a51cd51bc64))
* update ScheduleResource form and table for improved usability ([223c6cc](https://github.com/ibobdb/natunajaya/commit/223c6cc6a7add8114ac76c79e187ba7e2f0b18d2))
