import json
import csvutils
import codecs
import nolaflickr

if __name__ == '__main__':
  f = codecs.open('artists.txt','r','utf-8')
  all_authors = []
  for l in f:
    all_authors.append(l.strip())
  data = json.load(open('artist_info.json','r'))
  for a in data:
    name = a['name']
    a['summary'] = a.get('bio_summary','')
    a['image'] = a.get('image_large','')
    a['image_attrib'] = a.get('url','')
    a['livewire_id'] = a.get('mbid','')
    if name in all_authors:
      all_authors.remove(name)
  for name in all_authors:
    author = dict(name=name)
    flickr = nolaflickr.find_flickr_image(name)
    if flickr:
      author['image_attrib'] = flickr[0]
      author['image'] = flickr[1]
    data.append(author)
  data.sort(key=lambda x:x['name'])
  csvutils.write_csv('artists.csv',data,'name','tags','summary','url','livewire_id','image','image_attrib')
