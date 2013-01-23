#!/bin/bash

virtualenv scrapers

pushd scrapers
source bin/activate
pip install stdeb
pip install -r requirements.txt

mkdir target
popd
